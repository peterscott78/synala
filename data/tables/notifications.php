<?php

class table_notifications {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'none';

	// Define columns
	$this->columns = array(
		'checkbox' => "&nbsp;", 
		'display_name' => 'Name / Action', 
		'recipient' => 'Recipient', 
		'is_enabled' => 'Enabled', 
		'manage' => 'Manage'
	);
	$this->data = $data;

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total
	$this->total = DB::queryFirstField("SELECT count(*) FROM notifications");
	if ($this->total == '') { $this->total = 0; }

	// Return
	return $this->total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	$rows = DB::query("SELECT * FROM notifications ORDER BY id");

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$row['is_enabled'] = $row['is_enabled'] == 1 ? 'Yes' : 'No';
		$row['recipient'] = ucwords($row['recipient']);
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"notification_id[]\" value=\"$row[id]\"></center>";
		$row['manage'] = "<center><a href=\"" . SITE_URI . "/admin/setup/notifications_manage?notification_id=$row[id]\" class=\"btn btn-primary btn-xs\">Manage</a></center>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>