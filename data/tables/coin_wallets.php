<?php

class table_coin_wallets {

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
		'id' => 'ID', 
		'display_name' => 'Wallet Name', 
		'address_type' => 'Address Type', 
		'balance' => 'Balance'
	);
	$this->data = $data;
	$this->status = isset($data['status']) ? $data['status'] : 'pending';

	// Modify columns
	if (isset($data['no_checkbox']) && $data['no_checkbox'] == 1) { 
		unset($this->columns['checkbox']);
		unset($this->columns['id']);
	}

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total($start = 0) { 

	// Get total
	$this->total = DB::queryFirstField("SELECT count(*) FROM coin_wallets WHERE status = 'active'");
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
	$rows = DB::query("SELECT * FROM coin_wallets WHERE status = 'active' ORDER BY id");

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"wallet_id[]\" value=\"$row[id]\"></center>";
		$row['balance'] = $bip32->get_balance($row['id']) . ' BTC';
		if ($row['address_type'] == 'multisig') { 
			$row['address_type'] = 'Multisig - ' . $row['sigs_required'] . ' of ' . $row['sigs_total'];
		} else { $row['address_type'] = 'Standard'; }

		array_push($results, $row);
	}

	// Add total
	$total = DB::queryFirstField("SELECT count(*) FROM coin_wallets WHERE status = 'active'");
	if ($total > 1) { 

		// Get balance
		$total_balance = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE is_spent = 0");
		if ($total_balance == '') { $total_balance = 0 ; }

		// Set vars
		$vars = array(
			'checkbox' => "&nbsp;", 
			'id' => "&nbsp;", 
			'display_name' => '<b>Total</b>', 
			'address_type' => "&nbsp;", 
			'balance' => '<b>' . fmoney_coin($total_balance) . ' BTC</b>'
		);
		array_push($results, $vars);
	}

	// Return
	return $results;

}

}

?>