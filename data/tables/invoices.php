<?php

class table_invoices {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Initialize
	global $template;

	// Set variables
	$this->rows_per_page = 25;
	$this->pagination = 'bottom';

	// Define columns
	$this->columns = array(
		'checkbox' => '&nbsp;', 
		'user' => 'User', 
		'date_added' => 'Date', 
		'status' => 'Status', 
		'amount' => 'Amount', 
		'wallet' => 'Wallet', 
		'payment_address' => 'Address', 
		'manage' => 'Manage'
	);
	$this->data = $data;
	$this->status = isset($data['status']) ? $data['status'] : 'pending';
	$this->userid = isset($data['userid']) ? $data['userid'] : 0;

	// Change columns
	if ($this->status != 'pending') { 
		unset($this->columns['checkbox']);
	}
	if ($this->userid > 0) { 
		unset($this->columns['user']);
	} else { 
		unset($this->columns['status']);
	}
	if ($template->theme == 'public') { 
		unset($this->columns['checkbox']);
	}

	// Check # of wallets
	$count = DB::queryFirstField("SELECT count(*) FROM coin_wallets WHERE status = 'active'");
	if ($count < 2) { unset($this->columns['wallet']); }

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	if ($this->userid > 0) { 
		$this->total = DB::queryFirstField("SELECT count(*) FROM invoices WHERE userid = %d", $this->userid);
		if ($this->total == '') { $this->total = 0; }
	} else { 
		$this->total = DB::queryFirstField("SELECT count(*) FROM invoices WHERE status = %s", $this->status);
		if ($this->total == '') { $this->total = 0; }
	}

	// Return
	return $this->total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Initialize
	global $template;

	// Get rows to display
	if ($this->userid > 0) { 
		$rows = DB::query("SELECT * FROM invoices WHERE userid = $this->userid ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->userid);
	} else { 
		$rows = DB::query("SELECT * FROM invoices WHERE status = %s ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->status);
	}

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 
		$username = get_user($row['userid']);
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"invoice_id[]\" value=\"$row[id]\"></center>";
		$row['user'] = "<a href=\"" . SITE_URI . "/admin/users/manage2?username=$username\">$username</a>";
		$row['status'] = ucwords($row['status']);
		$row['date_added'] = fdate($row['date_added']);
		$row['amount'] = fmoney_coin($row['amount_btc']) . ' BTC (' . fmoney($row['amount']) . ')';
		$row['wallet'] = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);

		// Get URL
		$url = $template->theme == 'public' ? SITE_URI . "/account/view_invoice?invoice_id=$row[id]" : SITE_URI . "/admin/financial/invoices_manage?invoice_id=$row[id]";
		$row['manage'] = "<center><a href=\"$url\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>