<?php

// Set variales
define('VERSION', '0.1');
if (!isset($_SERVER['REQUEST_METHOD'])) { $_SERVER['REQUEST_METHOD'] = 'POST'; }
if (!isset($_GET['route'])) { $_GET['route'] = '/index'; }

// Get site path & URI  (directory to the software)
define('SITE_PATH', realpath(dirname(__FILE__)));
define('SITE_URI', preg_replace("/\/index\.php/", "", $_SERVER['PHP_SELF']));
ini_set('pcre.backtrack_limit', '4M');

// Register autoload function
spl_autoload_register('autoload_class');

// Security checks
//security_checks();

// Load needed files
require_once("data/config.php");
require_once("data/lib/functions.php");
require_once("data/lib/db/meekrodb.2.3.class.php");
require_once("data/lib/smarty/Smarty.class.php");

// Define database connection info (MeekroDB) -- connects to database upon first query
DB::$dbName = DBNAME;
DB::$user = DBUSER;
DB::$password = DBPASS;
DB::$host = DBHOST;
DB::$port = DBPORT;

// Load, if setup complete
$config = array('is_setup' => '0');
if (DBNAME != '') { 
	$result = DB::query("SELECT name,value FROM config");
	foreach ($result as $row) { $config[$row['name']] = $row['value']; }

	// Set default time zone
	date_default_timezone_set($config['timezone']);
}

// Set error handler
set_error_handler('error', E_ALL);

// Define registry
$registry = new stdClass();

/////////////////////////////////////////////////////////////////////////////
// Autoload classes
//     Automatically loads approriate file from /data/lib/classes/ directory 
//     when instance of new object is created.
/////////////////////////////////////////////////////////////////////////////

function autoload_class($class_name) {

	// Check if file exists
	$filename = SITE_PATH . '/data/lib/' . strtolower($class_name) . '.php';
	if (!file_exists($filename)) { return false; }

	// Load file
	require_once($filename);

}

/////////////////////////////////////////////////////////////////////////////
// Security Checks
//     Checks, destroys, and creates session as needed.
//     Performs any necessary CSRF checks.
//     Checks against attempted SQL / file injection attacks.
/////////////////////////////////////////////////////////////////////////////

function security_checks() { 

	// Start session
	if (isset($_SERVER['HTTP_USER_AGENT'])) { 

		// Start session
		session_start();

		// Check session string
		$session_hash = $_SERVER['HTTP_USER_AGENT'] . 'sd80234nm3pfjs092' . $_SERVER['HTTP_HOST'];
		if (isset($_SESSION['http_user'])) { 
			if ($_SESSION['http_user'] != md5($session_hash)) { session_destroy(); }
		} else { 
			$_SESSION['http_user'] = md5($session_hash);
		}

	}

	// Sanitize variables
	$_POST = sanitize($_POST);
	$_GET = sanitize($_GET);
	$_COOKIE = sanitize($_COOKIE);

}

/////////////////////////////////////////////////////////////////////
// Sanitize variable
//    $var - Variable to sanitize
/////////////////////////////////////////////////////////////////////

function sanitize($var) { 

	// Check for array
	if (is_array($var)) { 
		foreach ($var as $key => $value) { $var[$key] = sanitize($value); }
		return $var;
	}

	// Sanitize variable
	if (ini_get('magic_quotes_gpc') == 1) { $var = stripslashes($var); }

	// Restricted inputs
	$restricted = false;
	//if (preg_match("/SELECT |DROP |INSERT |CONCAT/si", $var)) { $restricted = true; }
	if (preg_match("/\%20sleep\%28|sleep\(/si", $var)) { $restricted = true; }
	elseif (preg_match("/etc\%2Fpasswd|etc\/passwd|etc\%2Fshadow|etc\/shadow|etc\%2Fgroup|etc\/group/si", $var)) { $restricted = true; }
	elseif (preg_match("/<script>|\%3cscript\%20|<style>|\%3cstyle\%20/si", $var)) { $restricted = true; }
	elseif (preg_match("/\\x/i", $var)) { $restricted = true; }
	elseif (preg_match("/OR(\s*?)(\d+)(.*?)\=(.*?)(\d+)/i", $var)) { $restricted = true; }

	// Process restricted
	if ($restricted === true) { 
		echo "We're sorry, but a potential security attack has been detected.  Please use your browser's back button, and try your request again.  Please be mindful of any special characters you're using, as they may be prohibited.\n";
		exit(0);
	}
	
	// Return
	return $var;
}

?>