<?php

class table_users_custom_fields {

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
		'display_name' => 'Field Name', 
		'form_field' => 'Form Field'
	);
	$this->data = $data;
	$this->status = isset($data['status']) ? $data['status'] : 'pending';

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total
	$this->total = DB::queryFirstField("SELECT count(*) FROM users_custom_fields");
	if ($this->total == '') { $this->total = 0; }

	// Return
	return $this->total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	$bip32 = new bip32();
	$rows = DB::query("SELECT * FROM users_custom_fields ORDER BY id");

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"custom_field_id[]\" value=\"$row[id]\"></center>";
		$row['form_field'] = ucwords($row['form_field']);
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>