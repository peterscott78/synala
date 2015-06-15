<?php

class table_coin_addresses {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'bottom';

	// Define columns
	$this->columns = array(
		'address' => 'Address', 
		'received' => 'Received', 
		'balance' => 'Balance', 
		'date_added' => 'Date Added'
	);
	$this->data = $data;
	$this->wallet_id = isset($_POST['wallet_id']) && $_POST['wallet_id'] > 0 ? $_POST['wallet_id'] : 0;
	$this->userid = isset($data['userid']) ? $data['userid'] : 0;

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	if (isset($_POST['search']) && $_POST['search'] != '') { 
		$total = DB::queryFirstField("SELECT count(*) FROM coin_addresses WHERE address LIKE %ss", $_POST['search']);
		if ($total == '') { $total = 0; }
	} elseif ($this->userid > 0) { 
		$total = DB::queryFirstField("SELECT count(*) FROM coin_addresses WHERE userid = %d", $this->userid);
		if ($total == '') { $total = 0; }
	} else { 
		$total = DB::queryFirstField("SELECT count(*) FROM coin_addresses");
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
	if (isset($_POST['search']) && $_POST['search'] != '') { 
		$rows = DB::query("SELECT * FROM coin_addresses WHERE address LIKE %ss ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $_POST['search']);
	} elseif ($this->userid > 0) { 
		$rows = DB::query("SELECT * FROM coin_addresses WHERE userid = %d ORDER BY date_added DESC LIMIT $start,$this->rows_per_page");
	} else { 
		$rows = DB::query("SELECT * FROM coin_addresses ORDER BY date_added DESC LIMIT $start,$this->rows_per_page");
	}

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 

		// Get balance
		$balance = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE is_spent = 0 AND address = %s", $row['address']);
		$row['balance'] = fmoney_coin($balance);

		// Set variables
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"input_id[]\" value=\"$row[id]\"></center>";
		$row['address'] = "<a href=\"" . SITE_URI . "/admin/financial/addresses_view?address=$row[address]\">$row[address]</a>";
		$row['date_added'] = fdate($row['date_added'], true);
		$row['received'] = fmoney_coin($row['total_input']);
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>