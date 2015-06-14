<?php

class table_orders {

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
		'status' => 'Status', 
		'username' => 'Username', 
		'product' => 'Product', 
		'amount' => 'Amount', 
		'manage' => 'Manage'
	);
	$this->data = $data;
	$this->status = isset($data['status']) ? $data['status'] : 'pending';
	$this->userid = isset($data['userid']) ? $data['userid'] : 0;

	// Modify columns
	if ($template->theme == 'public') { 
		unset($this->columns['checkbox']);
	}
	if ($this->userid == 0) { 
		unset($this->columns['status']);
	}
	if ($this->userid > 0) { 
		unset($this->columns['username']);
	}

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	if ($this->userid > 0) { 
		$total = DB::queryFirstField("SELECT count(*) FROM orders WHERE userid = %d", $this->userid);
		if ($total == '') { $total = 0; }
	} else { 
		$total = DB::queryFirstField("SELECT count(*) FROM orders WHERE status = %s", $this->status);
		if ($total == '') { $total = 0; }
	}

	// Return
	return $total;

}

///////////////////////////////////////////////////////////////////////////////////
// Get rows
///////////////////////////////////////////////////////////////////////////////////

public function get_rows($start = 0) { 

	// Initialize
	global $template;

	// Get rows to display
	if ($this->userid > 0) { 
		$rows = DB::query("SELECT * FROM orders WHERE userid = %d ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->userid);
	} else { 
		$rows = DB::query("SELECT * FROM orders WHERE status = %s ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->status);
	}

	// Go through rows
	$results = array();
	foreach ($rows as $row) {
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"order_id[]\" value=\"$row[id]\"></center>";
		$row['date_added'] = fdate($row['date_added'], true);
		$row['product'] = DB::queryFirstField("SELECT display_name FROM products WHERE id = %d", $row['product_id']);
		$row['amount'] = fmoney_coin($row['amount_btc']) . ' BTC (' . fmoney($row['amount']) . ')';
		$row['status'] = ucwords($row['status']);

		// Get manage URL
		$url = $template->theme == 'public' ? SITE_URI . "/account/view_order?order_id=$row[id]" : SITE_URI . "/admin/financial/orders_manage?order_id=$row[id]";
		$row['manage'] = "<center><a href=\"$url\" class=\"btn btn-primary btn-xs\">Manage</a></center>";

		$username = get_user($row['userid']);
		$row['username'] = "<a href=\"" . SITE_URI . "/admin/user/manage2?username=$username\">$username</a>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>