<?php

// Initialize
global $template, $currency;

// Get invoice
if (!$row = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $_REQUEST['invoice_id'])) { 
	trigger_error("Invoice does not exist, ID# $_REQUST[invoice_id]", E_USER_ERROR);
}

// Set variables
$row['wallet_name'] = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
$row['username'] = get_user($row['userid']);
$row['date_added'] = fdate($row['date_added']);
$row['date_paid'] = preg_match("/^0000/", $row['date_paid']) ? 'Unpaid' : fdate($row['date_paid']);

// Radio chks
if ($row['currency'] == 'fiat') { 
	$template->assign('chk_currency_fiat', 'checked="checked"');
	$template->assign('chk_currency_btc', '');
	$row['primary_amount'] = $row['amount'];
	$row['alt_amount'] = $row['amount_btc'];
	$row['alt_currency'] = 'BTC';
} else {
	$template->assign('chk_currency_fiat', '');
	$template->assign('chk_currency_btc', 'checked="checked"');
	$row['primary_amount'] = $row['amount_btc'];
	$row['alt_amount'] = $row['amount'];
	$row['alt_currency'] = $config['currency'];
}

// Status options
if ($row['status'] == 'paid') { $status_options = '<option value="pending">Pending<option value="paid" selected="selected">Paid<option value="cancelled">Cancelled'; }
elseif ($row['status'] == 'cancelled') { $status_options = '<option value="pending">Pending<option value="paid">Paid<option value="cancelled" selected="selected">Cancelled'; }
else { $status_options = '<option value="pending">Pending<option value="paid">Paid<option value="cancelled">Cancelled'; }


// Template variables
$template->assign('invoice', $row);
$template->assign('status_options', $status_options);

?>