<?php

class table_products {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'bottom';

	// Define columns
	$this->columns = array(
		'checkbox' => "&nbsp;", 
		'display_name' => 'Product Name', 
		'amount' => 'Price'
	);
	$this->data = $data;
	$this->is_enabled = isset($data['is_enabled']) ? $data['is_enabled'] : 1;

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	$total = DB::queryFirstField("SELECT count(*) FROM products WHERE is_enabled = %d", $this->is_enabled);
	if ($total == '') { $total = 0; }

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	$rows = DB::query("SELECT * FROM products WHERE is_enabled = %d ORDER BY display_name LIMIT $start,$this->rows_per_page", $this->is_enabled);

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"product_id[]\" value=\"$row[id]\"></center>";
		$row['amount'] = $row['currency'] == 'fiat' ? fmoney($row['amount']) : fmoney_coin($row['amount']) . ' BTC';
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>