<?php

class table_coin_unauthorized_sends {

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
		'date_added' => 'Date', 
		'amount' => 'Amount', 
		'user' => 'User', 
		'address' => 'Address', 
		'viewtx' => 'View Tx'
	);
	$this->data = $data;

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	$total = DB::queryFirstField("SELECT count(*) FROM coin_unauthorized_sends");
	if ($total == '') { $total = 0; }

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	$rows = DB::query("SELECT * FROM coin_unauthorized_sends ORDER BY date_added DESC LIMIT $start,$this->rows_per_page");

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$irow = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE id = %d", $row['input_id']);
		$username = get_user($irow['userid']);

		$row['date_added'] = fdate($row['date_added'], true);
		$row['amount'] = fmoney_coin($irow['amount']) . ' BTC';
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"unauthorized_send_id[]\" value=\"$row[id]\"></center>";
		$row['user'] = "<a href=\"" . SITE_URI . "/admin/user/manage2?username=$username\">$username</a>";
		$row['address'] = "<a href=\"" . SITE_URI . "/admin/financial/addresses_view?address=$irow[address]\">$irow[address]</a>";
		$row['viewtx'] = "<center><a href=\"" . SITE_URI . "/admin/financial/tx?txid=$row[txid]\" class=\"btn btn-primary btn-xs\">View Tx</a></center>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>