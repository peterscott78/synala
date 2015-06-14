<?php

class table_coin_inputs {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Initialize
	global $template;

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'bottom';

	// Define columns
	$this->columns = array(
		'checkbox' => "&nbsp;", 
		'date_added' => 'Date', 
		'wallet' => 'Wallet', 
		'amount' => 'Amount', 
		'user' => 'User', 
		'address' => 'Address', 
		'confirmations' => 'Confirmations', 
		'viewtx' => 'View Tx'
	);
	$this->data = $data;
	$this->wallet_id = isset($data['wallet_id']) ? $data['wallet_id'] : 0;
	$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
	$this->address = isset($data['address']) ? $data['address'] : '';
	$this->userid = isset($data['userid']) ? $data['userid'] : 0;
	$this->order_id = isset($data['order_id']) ? $data['order_id'] : 0;

	// Modify columns
	if ($this->wallet_id == 0) { 
		$count = DB::queryFirstField("SELECT count(*) FROM coin_wallets WHERE status = 'active'");
		if ($count < 2) { unset($this->columns['wallet']); }
	}
	if ($this->is_new != 1) { unset($this->columns['checkbox']); }
	if ($this->address != '') {
		unset($this->columns['address']);
		unset($this->columns['user']);
	}
	if ($this->order_id > 0) { 
		unset($this->columns['user']);
	}
	if ($template->theme == 'public') { 
		unset($this->columns['user']);
	}

	// Get where SQL
	$this->where_sql = $this->get_where_sql();

}

///////////////////////////////////////////////////////////////////////////////////
// Get where SQL
///////////////////////////////////////////////////////////////////////////////////

public function get_where_sql() { 

	// Get SQL
	$where_sql = 'is_change = 0 AND ';
	if ($this->wallet_id > 0) { $where_sql .= "wallet_id = $this->wallet_id AND "; }
	if ($this->is_new == 1) { $where_sql .= "is_new = 1 AND "; }
	if ($this->address != '') { $where_sql .= "address = '$this->address' AND "; }
	if ($this->userid > 0) { $where_sql .= "userid = $this->userid AND "; }
	if ($this->order_id > 0) { $where_sql .= "order_id = $this->order_id AND "; }

	// Return
	$where_sql = preg_replace("/ AND $/", "", $where_sql);
	return $where_sql;

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	$total = DB::queryFirstField("SELECT count(*) FROM coin_inputs WHERE $this->where_sql");
	if ($total == '') { $total = 0; }

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Initailize
	global $template;

	// Get rows to display
	$rows = DB::query("SELECT * FROM coin_inputs WHERE $this->where_sql ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->wallet_id);

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 

		// Parse date
		list($date, $time) = explode(" ", $row['date_added']);
		list($year, $month, $day) = explode("-", $date);
		list($hour, $min, $sec) = explode(":", $time);
		$row['date_added'] = date('M jS H:i', mktime($hour, $min, $sec, $month, $day, $year));

		// Get URLs
		$addr_url = $template->theme == 'public' ? SITE_URI . "/account/address?address=$row[address]" : SITE_URI . "/admin/financial/addresses_view?address=$row[address]";
		$tx_url = $template->theme == 'public' ? SITE_URI . "/account/tx?txid=$row[txid]" : SITE_URI . "/admin/financial/tx?txid=$row[txid]";

		// Set variables
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"input_id[]\" value=\"$row[id]\"></center>";
		$row['viewtx'] = "<center><a href=\"$tx_url\" class=\"btn btn-primary btn-xs\">View Tx</a></center>";
		$row['wallet'] = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
		$row['address'] = "<a href=\"$addr_url\">$row[address]</a>";

		$username = get_user($row['userid']);
		$row['user'] = "<a href=\"" . SITE_URI . "/admin/user/manage2?username=$username\">$username</a>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>