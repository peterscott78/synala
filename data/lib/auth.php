<?php

class auth {

//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct() { 

	// Set blank variables
	$this->userid = 0;

}

//////////////////////////////////////////////////////////////////////////
// Check login
//////////////////////////////////////////////////////////////////////////

public function check_login($type = 'public', $login_required = false) { 

	// Initialize
	global $config;

	// Expire needed sessions
	DB::query("DELETE FROM auth_sessions WHERE last_active < %d", (time() - ($config['session_expire_mins'] * 60)));

	// Check for session
	$cookie_name = COOKIE_NAME . 'auth_hash';
	if (isset($_COOKIE[$cookie_name]) && $row = DB::queryFirstRow("SELECT * FROM auth_sessions WHERE auth_hash = %s", hash('sha512', $_COOKIE[$cookie_name]))) { 
		DB::query("UPDATE auth_sessions SET last_active = %d WHERE id = %d", time(), $row['id']);
		return $row['userid'];

	} elseif (((isset($_POST['submit']) && $_POST['submit'] == tr('Login Now')) || preg_match("/login$/", $_GET['route'])) && $_SERVER['REQUEST_METHOD'] == 'POST') { 
		return $this->login($type);

	} elseif ($login_required === true) {

		if ($type == 'admin') { 
			$template = new template('admin/login', 'admin');
			echo $template->parse(); exit(0);
		} else { 
			$template = new template('login');
			echo $template->parse(); exit(0);
		}
	}

	// Return
	return false;

}

//////////////////////////////////////////////////////////////////////////
// Login
//////////////////////////////////////////////////////////////////////////

private function login($type = 'public') { 

	// Get user row
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", strtolower($_POST['username']))) { 
		$this->invalid_login($type);
	}

	// Check password
	$client = new encrypt();
	if ($client->get_password_hash($_POST['password'], $user_row['id']) != $user_row['password']) {
		$this->invalid_login($type);
	}

	// Get session ID
	do {
		$session_id = generate_random_string(60);
		$exists = DB::queryFirstRow("SELECT * FROM auth_sessions WHERE auth_hash = %s", hash('sha512', $session_id)) ? 1 : 0;
	} while ($exists > 0);

	// Create session
	DB::insert('auth_sessions', array(
		'userid' => $user_row['id'], 
		'last_active' => time(), 
		'auth_hash' => hash('sha512', $session_id))
	);

	// Set cookie
	$cookie_name = COOKIE_NAME . 'auth_hash';
	setcookie($cookie_name, $session_id);

	// Update alerts
	DB::query("UPDATE alerts SET is_new = 0 WHERE is_new = 2 AND userid = %d", $user_row['id']);
	DB::query("UPDATE alerts SET is_new = 2 WHERE is_new = 1 AND userid = %d", $user_row['id']);

	// Redirect user
	if ($type == 'admin') { 
		header("Location: " . SITE_URI . "/admin/index");
		exit(0);
	}
	
	// Return
	return $user_row['id'];

}

//////////////////////////////////////////////////////////////////////////
// Invalid login
//////////////////////////////////////////////////////////////////////////

private function invalid_login($type = 'public') {

	// Init template
	if ($type == 'admin') { $template = new template('admin/login'); }
	else { $template = new template('login'); }

	// User message
	$template->add_message("Incorrect username or password specified.  Please try again.", 'error');
	$template->parse();
	exit(0);

}

//////////////////////////////////////////////////////////////////////////
// Logout
//////////////////////////////////////////////////////////////////////////

public function logout() { 

	// Check
	$cookie_name = COOKIE_NAME . 'auth_hash';
	if (!isset($_COOKIE[$cookie_name])) { return false; }

	// Delete session
	DB::query("DELETE FROM auth_sessions WHERE auth_hash = %s", hash('sha512', $_COOKIE[$cookie_name]));

	// Set cookie
	setcookie($cookie_name);
	$GLOBALS['userid'] = 0;

	// Return
	return true;

}

}

?>