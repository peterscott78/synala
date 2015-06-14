<?php

class table_users {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Initialize
	global $config;

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'bottom';

	// Define columns
	$this->columns = array(
		'username' => 'Username', 
		'full_name' => 'Full Name', 
		'email' => 'E-Mail', 
		'group' => 'Group', 
		'date_created' => 'Join Date'
	);
	$this->data = $data;
	$this->is_search = isset($data['is_search']) ? $data['is_search'] : 0;
	if (!isset($_POST['username'])) { $_POST['username'] = ''; }

	// Get groups
	$this->groups = array();
	$rows = DB::query("SELECT id,name FROM users_groups");
	foreach ($rows as $row) { 
		$this->groups[$row['id']] = $row['name'];
	}

	// Modify columns
	if ($config['enable_full_name'] != 1) { 
		unset($this->columns['full_name']);
	}
	if ($config['username_field'] == 'email') { 
		unset($this->columns['email']);
	}

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	if ($this->is_search == 1) { 
		$total = DB::queryFirstField("SELECT count(*) FROM users WHERE username LIKE %ss OR email LIKE %ss", $_POST['username'], $_POST['usernme']);
		if ($total == '') { $total = 0; }
	} else { 
		$total = DB::queryFirstField("SELECT count(*) FROM users");
		if ($total == '') { $total = 0; }
	}

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	if ($this->is_search == 1) { 
		$rows = DB::query("SELECT * FROM users WHERE username LIKE %ss OR email LIKE %ss ORDER BY username LIMIT $start,$this->rows_per_page", $_POST['username'], $_POST['username']);
	} else { 
		$rows = DB::query("SELECT * FROM users ORDER BY username LIMIT $start,$this->rows_per_page");
	}

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$row['date_created'] = fdate($row['date_created']);
		$row['group'] = $this->groups[$row['group_id']];

		$row['username'] = "<a href=\"" . SITE_URI . "/admin/user/manage2?username=$row[username]\">$row[username]</a>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>