<?php

class table_alerts {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct($data = array()) { 

	// Initialize
	global $template;

	// Set variables
	$this->rows_per_page = 50;
	$this->pagination = 'bottom';

	// Set data
	$this->data = $data;
	$this->type = $data['type'];

	// Define columns
	$this->columns = array(
		'checkbox' => "&nbsp;", 
		'date_added' => 'Date', 
		'username' => 'Username'
	);

	if ($this->type == 'new_user') { 
		$this->columns['email'] = 'E-Mail';
	} elseif ($this->type == 'new_deposit') { 
		$this->columns['amount'] = 'Amount';
		$this->columns['viewtx'] = 'View Tx';
	} elseif ($this->type == 'product_purchase') { 
		$this->columns['product'] = 'Product';
		$this->columns['amount'] = 'Amount';
		$this->columns['manage'] = 'Manage';
	} elseif ($this->type == 'invoice_paid') { 
		$this->columns['invoice'] = 'Invoice';
		$this->columns['amount'] = 'Amount';
		$this->columns['manage'] = 'Manage';
	}

}

///////////////////////////////////////////////////////////////////////////////////
// Get total
///////////////////////////////////////////////////////////////////////////////////

public function get_total() { 

	// Get total rows
	$total = DB::queryFirstField("SELECT count(*) FROM alerts WHERE type = %s AND userid = %d", $this->type, $GLOBALS['userid']);
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
	$rows = DB::query("SELECT * FROM alerts WHERE type = %s AND userid = %d ORDER BY date_added DESC LIMIT $start,$this->rows_per_page", $this->type, $GLOBALS['userid']);

	// Go through rows
	$results = array();
	foreach ($rows as $row) { 

		// Get URLs
		$addr_url = $template->theme == 'public' ? SITE_URI . "/account/address?address=$row[address]" : SITE_URI . "/admin/financial/addresses_view?address=$row[address]";

		// Set variables
		$row['checkbox'] = "<center><input type=\"checkbox\" name=\"alert_id[]\" value=\"$row[id]\"></center>";
		$row['date_added'] = fdate($row['date_added'], true);

		// Type specific variables
		if ($this->type == 'new_user') { 
			$user_row = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $row['reference_id']);
			$row['username'] = $user_row['username'];
			$row['email'] = $user_row['email'];
		} else {
			$input = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE id = %d", $row['reference_id']);
			$row['username'] = get_user($input['userid']);
			$row['amount'] = fmoney_coin($input['amount']) . ' BTC';
			$row['viewtx'] = "<center><a href=\"" . SITE_URI . "/admin/financial/tx?txid=" . $input['txid'] . "\" class=\"btn btn-primary btn-xs\">View Tx</a></center>";

			if ($this->type == 'product_purchase') { 
				$row['product'] = DB::queryFirstField("SELECT display_name FROM products WHERE id = %d", $input['product_id']);
				$row['manage'] = "<center><a href=\"" . SITE_URI . "/admin/financial/orders_manage?order_id=" . $input['order_id'] . "\" class=\"btn btn-primary btn-xs\">Manage</a></center>";

			} elseif ($this->type == 'invoice_paid') { 
				$irow = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $input['invoice_id']);
				$row['invoice'] = "ID# $input[invoice_id] (added: " . fdate($invoice['date_added']) . ")";
				$row['manage'] = "<center><a href=\"" . SITE_URI . "/admin/financial/invoices_manage?invoice_id=" . $input['invoice_id'] . "\" class=\"btn btn-primary btn-xs\">Manage</a></center>";

			}
		}
		//$row['address'] = "<a href=\"$addr_url\">$row[address]</a>";

		$row['username'] = "<a href=\"" . SITE_URI . "/admin/user/manage2?username=$row[username]\">$row[username]</a>";
		array_push($results, $row);
	}

	// Return
	return $results;

}

}

?>