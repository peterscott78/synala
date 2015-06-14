<?php

// Initialize
global $template, $currency;

// Get invoice
if (!$row = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $_REQUEST['invoice_id'])) { 
	trigger_error("Invoice does not exist, ID# $_REQUST[invoice_id]", E_USER_ERROR);
}

// Set variables
$row['wallet_name'] = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
$row['status'] = ucwords($row['status']);
$row['date_added'] = fdate($row['date_added']);
$row['date_paid'] = preg_match("/^0000/", $row['date_paid']) ? 'Unpaid' : fdate($row['date_paid']);
$row['note'] = str_replace("\n", "<br />", $row['note']);
$row['amount'] = fmoney($row['amount']);
$row['amount_btc'] = fmoney_coin($row['amount_btc']);

// Template variables
$template->assign('invoice', $row);

?>