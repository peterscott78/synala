<?php

class controller_default { 

public function __construct($parts = array()) { 

	// Initialize
	global $config, $template;

	// Set variables
	if ($config['is_setup'] == 1 && preg_match("/^admin/", trim($_GET['route'], '/'))) { 
		$panel = 'admin';
		$require_login = true;
	} else { 
		$panel = 'public';
		$require_login = false;
	}

	// Check IP restrictions
	if ($panel == 'admin' && isset($config['ipallow']) && $config['ipallow'] != '') { 

		$ok = false;
		$ips = explode("\n", $config['ipallow']);
		foreach ($ips as $ip) { 
			if (preg_match("/^$ip/", $_SERVER['REMOTE_ADDR'])) { $ok = true; break; }
		}
		if ($ok === false) { echo "Access dened by IP restrictions."; exit(0); }
	}

	// Continue setup, if needed
	if (DBNAME == '' && isset($_POST['submit']) && $_POST['submit'] == tr('Continue to Next Step')) { 

		// Initialize
		$template = new template('admin/setup/first_time2');
		require_once(SITE_PATH . '/data/lib/sqlparser.php');

		// Check database connection
		if (!mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $_POST['dbport'])) { 
			$template->add_message("Unable to connect to mySQL database using information supplied.  Please double check the mySQL information, and try again.", 'error');
		}
		if (!is_writeable(SITE_PATH . '/data/config.php')) { $template->add_message("Unable to write to file at /data/config.php.  Please change file permissions appropriately, and reload the page.", 'error'); }
		if (!is_writeable(SITE_PATH . '/data/backups')) { $template->add_message("Unable to write to directory at /data/backups/.  Please change directory permissions appropriately, and reload the page.", 'error'); }
		if (!is_writeable(SITE_PATH . '/data/log')) { $template->add_message("Unable to write to directory at /data/log/.  Please change directory permissions appropriately, and reload the page.", 'error'); }
		if (!is_writeable(SITE_PATH . '/data/tpl_c')) { $template->add_message("Unable to write to directory at /data/tpl_c/.  Please change directory permissions appropriately, and reload the page.", 'error'); }

		// Check for errors
		if ($template->has_errors == 1) { 
			$template->route = 'admin/setup/first_time';
			echo $template->parse(); exit(0);
		}

		// Define MeekroDB settings
		DB::$dbName = $_POST['dbname'];
		DB::$user = $_POST['dbuser'];
		DB::$password = $_POST['dbpass'];
		DB::$host = $_POST['dbhost'];
		DB::$port = $_POST['dbport'];

		// Parse sql
		$sql_lines = SqlParser::parse(file_get_contents(SITE_PATH . '/data/sql/install.sql'));
		foreach ($sql_lines as $line) { 
			DB::query($line);
		}

		// Save config.php file
		$conf = "<?php\n";
		$conf .= "define('DBNAME', '" . $_POST['dbname'] . "');\n";
		$conf .= "define('DBUSER', '" . $_POST['dbuser'] . "');\n";
		$conf .= "define('DBPASS', '" . $_POST['dbpass'] . "');\n";
		$conf .= "define('DBHOST', '" . $_POST['dbhost'] . "');\n";
		$conf .= "define('DBPORT', '" . $_POST['dbport'] . "');\n";
		$conf .= "define('COOKIE_NAME', '" . generate_random_string(6) . "');\n";
		$conf .= "define('ENCRYPT_PASS', '" . generate_random_string(32) . "');\n";
		$conf .= "define('TESTNET', 0);\n";
		$conf .= "?>\n";

		// Save config file
		file_put_contents(SITE_PATH . '/data/config.php', $conf);

		// Parse template
		echo $template->parse();
		exit(0);

	} elseif ($config['is_setup'] != '1' && isset($_POST['_setup_step']) && $_POST['_setup_step'] == '2') { 

		// Initialize
		$template = new template('admin/setup/first_time3');
		if (strlen($_POST['username']) < 4) { $template->add_message('Administrator username must be at least 4 characters in length.', 'error'); }

		// Create user
		$user = new user();
		$user->create(1);

		// Update config vars
		update_config_var('site_name', $_POST['site_name']);
		update_config_var('company_name', $_POST['company_name']);

		// Check for errors
		if ($template->has_errors == 1) { 
			$template->route = 'admin/setup/first_time2';
		} else { 

			// Login
			$auth = new auth();
			$auth->login('admin', false);

		}
		echo $template->parse();
		exit(0);

	} elseif ($config['is_setup'] != '1' && isset($_POST['_setup_step']) && $_POST['_setup_step'] == '3') { 

		// Initialize
		$template = new template('admin/setup/first_time4');

		// Update config vars
		update_config_var('btc_rpc_host', $_POST['btc_rpc_host']);
		update_config_var('btc_rpc_user', $_POST['btc_rpc_user']);
		update_config_var('btc_rpc_pass', $_POST['btc_rpc_pass']);
		update_config_var('btc_rpc_port', $_POST['btc_rpc_port']);

		// Test connection
		$client = new transaction();
		if (!$client->get_info()) {
			$template->route = 'admin/setup/first_time3';
			$template->add_message('Unable to connect to RPC using the provided settings.  Please check the connection information, restart bitcoind, and try again.  If you have just started bitcoind for the first time, you will need to wait a while for all blocks to download before continuing.', 'error');
			$template->parse(); exit(0);
		}

		// Parse template
		echo $template->parse();
		exit(0);

	// Complete setup, if needed
	} elseif ($config['is_setup'] != '1' && isset($_POST['_setup_step']) && $_POST['_setup_step'] == '4') { 

		// Initialize
		$template = new template('admin/setup/first_time5');

		// Update config vars
		update_config_var('is_setup', '1');

		// Get exchange date
		$rate = get_coin_exchange_rate($config['currency']);
		if ($rate != 0) { update_config_var('exchange_rate', $rate); }

		// Add wallet
		$bip32 = new bip32();
		$bip32->add_wallet();

		// Display template
		if ($template->has_errors != 1) { 
			//$template->add_message("Successfully completed first time setup.");
		}
		echo $template->parse();
		exit(0);
	} 

	// Check if setup
	if ($config['is_setup'] == 0) { 
		$template = new template('admin/setup/first_time');
		echo $template->parse(); exit(0);
	}

	// Check login
	$auth = new auth();
	if ($userid = $auth->check_login($panel, $require_login)) { 
		define('LOGIN', true);
		$GLOBALS['userid'] = $userid;
	} else { 
		define('LOGIN', false);
		$GLOBALS['userid'] = 0;
	}

	// Check admin permission, if needed
	if ($panel == 'admin') { 
		$group_id = DB::queryFirstField("SELECT group_id FROM users WHERE id = %d", $GLOBALS['userid']);
		if ($group_id != 1) { trigger_error("You do not have permission to access this area.", E_USER_ERROR); }
	}

	// Parse template
	$template = new template();
	echo $template->parse();

	// Exit
	exit(0);
	

}

}

?>