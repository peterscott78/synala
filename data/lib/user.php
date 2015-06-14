<?php

class user {

//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct($userid = 0) { 
	$this->userid = $userid;
}

//////////////////////////////////////////////////////////////////////////
// Create
//////////////////////////////////////////////////////////////////////////

public function create($group_id = 2) { 

	// Initialize
	global $template, $config;

	// Validate profile
	$this->validate_profile();
	if ($template->has_errors == 1) { return 0; }

	// Set variables
	$reg_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
	$full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';

	// Get custom fields
	$custom_fields = array();
	$rows = DB::query("SELECT * FROM users_custom_fields ORDER BY id");
	foreach ($rows as $row) { 
		$var = 'custom' . $row['id'];
		if (!isset($_POST[$var])) { continue; }
		$custom_fields[$var] = $_POST[$var];
	}

	// Add to DB
	DB::insert('users', array(
		'username' => $_POST['username'], 
		'full_name' => $full_name, 
		'email' => $_POST['email'], 
		'password' => '*', 
		'group_id' => $group_id, 
		'reg_ip' => $reg_ip, 
		'custom_fields' => serialize($custom_fields))
	);
	$this->userid = DB::insertId();

	// Update password
	$client = new encrypt();
	$password = $client->get_password_hash($_POST['password'], $this->userid);
	DB::update('users', array(
		'password' => $password), 
	"id = %d", $this->userid);

	// Add alerts
	add_alert('new_user', $this->userid);

	// Execute hooks
	execute_hooks('new_user', $this->userid);

	// Return
	return $this->userid;

}

//////////////////////////////////////////////////////////////////////////
// Validate profile
//////////////////////////////////////////////////////////////////////////

public function validate_profile() { 

	// Initialize
	global $template, $config;

	// Set variables
	if ($config['username_field'] == 'email') { $_POST['username'] = $_POST['email']; }
	$_POST['username'] = strtolower($_POST['username']);
	$_POST['email'] = strtolower($_POST['email']);

	// Perform checks
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) { $template->add_message("Invalid e-mail address, $_POST[email]", 'error'); }
	if ($_POST['password'] == '') { $template->add_message('You did not specify a password.', 'error'); }
	if ($_POST['password'] != $_POST['password2']) { $template->add_message('Passwords do not match.  Please try again.', 'error'); }

	// Check if username exists
	$exists = DB::queryFirstField("SELECT count(*) FROM users WHERE username = %s", $_POST['username']);
	if ($exists > 0) { $template->add_message("Username already exist, $_POST[username].  Please try with a different username.", 'error'); }

}

//////////////////////////////////////////////////////////////////////////
// Load profile
//////////////////////////////////////////////////////////////////////////

public function load() { 

	// Get row
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $this->userid)) { 
		trigger_error("User does not exist, ID# $this->userid", E_USER_ERROR);
	}

	// Return
	return $user_row;

}
//////////////////////////////////////////////////////////////////////////
// Update
//////////////////////////////////////////////////////////////////////////

public function update() { 

	// Initialize
	global $template, $config;

	// Checks
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) { $template->add_message("Invalid e-mail address, $_POST[email]", 'error'); }

	// Set variables
	$updates = array();
	if ($config['username_field'] == 'email') { $_POST['new_username'] = $_POST['email']; }
	$old_username = DB::queryFirstField("SELECT username FROM users WHERE id = %d", $this->userid);

	// Set updates array
	if ($old_username != $_POST['new_username']) { 
		if ($row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", strtolower($_POST['new_username']))) { 
			$template->add_message("Unable to change username, as username already exists, $_POST[new_username]", 'error');
		} else { $updates['username'] = strtolower($_POST['new_username']); }
	}

	// Set other variables
	if (isset($_POST['is_admin'])) { $updates['group_id'] = $_POST['is_admin'] == 1 ? 1 : 2; }
	if (isset($_POST['is_active'])) { $updates['status'] = $_POST['is_active'] == 1 ? 'active' : 'inactive'; }
	if (isset($_POST['full_name'])) { $updates['full_name'] = $_POST['full_name']; }
	$updates['email'] = strtolower($_POST['email']);

	// Update password, if needed
	if ($_POST['password'] != '' && $_POST['password'] == $_POST['password2']) { 
		$client = new encrypt();
		$updates['password'] = $client->get_password_hash($_POST['password'], $this->userid);
	}

	// Get custom fields
	$custom_fields = array();
	$rows = DB::query("SELECT * FROM users_custom_fields ORDER BY id");
	foreach ($rows as $row) { 
		$var = 'custom' . $row['id'];
		if (!isset($_POST[$var])) { continue; }
		$custom_fields[$var] = $_POST[$var];
	}
	$updates['custom_fields'] = serialize($custom_fields);

	// Update database
	if ($template->has_errors != 1) { 
		DB::update('users', $updates, "id = %d", $this->userid);
		return true;
	} else { return false; }

}


}

?>