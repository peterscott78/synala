<?php

class table_coin_sends {

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
		'recipients' => 'Recipients', 
		'sign' => 'Sign Tx', 
		'viewtx' => 'View Tx'
	);
	$this->data = $data;
	$this->status = isset($data['status']) ? $data['status'] : 'pending';

	// Modify columns
	if ($this->status != 'pending') {
		unset($this->columns['checkbox']);
		unset($this->columns['sign']);
	}
	if ($this->status != 'sent') { 
		unset($this->columns['viewtx']);
	}

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	$total = DB::queryFirstField("SELECT count(*) FROM coin_sends WHERE status = %s", $this->status);
	if ($total == '') { $total = 0; }

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Get rows to display
	$rows = DB::query("SELECT * FROM coin_sends WHERE status = %s ORDER BY date_added LIMIT $start,$this->rows_per_page", $this->status);

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 

		// Get recipients
		$row['recipients'] = '';
		$addr_rows = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d ORDER BY id", $row['id']);
		foreach ($addr_rows as $arow) { 
			$row['recipients'] .= $arow['address'] . " -- " . $arow['amount'];
		}

		// Add row to results
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"send_id[]\" value=\"$row[id]\"></center>";
		$row['date_added'] = fdate($row['date_added'], true);
		$row['sign'] = "<center><a href=\"" . SITE_URI . "/admin/financial/sign_tx?send_id=$row[id]\" class=\"btn btn-primary btn-xs\">Sign Tx</a></center>";
		$row['viewtx'] = "<center><a href=\"" . SITE_URI . "/admin/financial/tx?txid=$row[txid]\" class=\"btn btn-primary btn-xs\">View Tx</a></center>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>