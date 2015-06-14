<?php

// Initialize
global $template;

// Get tx
$client = new transaction();
if (!$trans = $client->get_tx($_REQUEST['txid'])) { 
	trigger_error("Txid does not exist in blockchain, $_REQUEST[txid]", E_USER_ERROR);
}
if (!isset($trans['blocknum'])) { $trans['blocknum'] = 'Unconfirmed'; }
if (!isset($trans['confirmations'])) { $trans['confirmations'] = 0; }

// Get input
$is_input = false;
if ($row = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE txid = %s", $_REQUEST['txid'])) { 
	$is_input = true;
	$is_order = $row['order_id'] > 0 ? true : false;
	$is_invoice = $row['invoice_id'] > 0 ? true : false;
	$username = $row['userid'] == 0 ? '-' : get_user($row['userid']);

	// Get order details
	if ($is_order === true) { 
		$orow = DB::queryFirstRow("SELECT * FROM orders WHERE id = %d", $row['order_id']);
		$product_name = DB::queryFirstField("SELECT display_name FROM products WHERE id = %d", $orow['product_id']);
		$order_details = "<a href=\"" . SITE_URI . "/admin/financial/orders_manage?order_id=$orow[id]\">ID# $orow[id] -- $product_name</a>";
	} else { $order_details = ''; }

	// Get invoice details
	if ($is_invoice === true) { 
		$irow = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $row['invoice_id']);
		$invoice_details = "<a href=\"" . SITE_URI . "/admin/financial/invoices_manage?invoice_id=$irow[id]\">ID# $irow[id] -- " . fmoney($irow['amount']) . ' (added on ' . fdate($irow['date_added']) . ")</a>";
	} else { $invoice_details = ''; }

	// Set vars
	$payment = array(
		'is_order' => $is_order, 
		'is_invoice' => $is_invoice, 
		'username' => $username, 
		'date_received' => fdate($row['date_added'], true), 
		'amount' => fmoney_coin($row['amount']) . ' BTC ', 
		'order_details' => $order_details, 
		'invoice_details' => $invoice_details
	);
	$template->assign('payment', $payment);
}

// Template variables
$template->assign('is_input', $is_input);
$template->assign('txid', $trans['txid']);
$template->assign('confirmations', $trans['confirmations']);
$template->assign('blocknum', $trans['blocknum']);
$template->assign('input_amount', $trans['input_amount']);
$template->assign('output_amount', $trans['output_amount']);
$template->assign('fees', $trans['fees']);
$template->assign('inputs', $trans['inputs']);
$template->assign('outputs', $trans['outputs']);

?>