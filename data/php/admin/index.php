<?php

// Initialize
global $template;

// Check updates
check_updates();

// Get totals
$total_funds_received = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE is_change = 0");
$total_new_deposits = DB::queryFirstField("SELECT count(*) FROM alerts WHERE type = 'new_deposit' AND userid = %d", $GLOBALS['userid']);
$total_new_deposits_amount = DB::queryFirstField("SELECT sum(amount) FROM alerts WHERE type = 'new_deposit' AND userid = %d", $GLOBALS['userid']);
$total_users = DB::queryFirstField("SELECT count(*) FROM users WHERE status != 'deleted'");
$new_users = DB::queryFirstField("SELECT count(*) FROM alerts WHERE type = 'new_user' AND userid = %d", $GLOBALS['userid']);
$total_products = DB::queryFirstField("SELECT count(*) FROM orders WHERE status != 'declined'");
$total_products_amount = DB::queryFirstField("SELECT sum(amount_btc) FROM orders WHERE status != 'declined'");
$new_products = DB::queryFirstField("SELECT count(*) FROM alerts WHERE type = 'product_purchase' AND userid = %d", $GLOBALS['userid']);
$new_products_amount = DB::queryFirstField("SELECT sum(amount) FROM alerts WHERE type = 'product_purchase' AND userid = %d", $GLOBALS['userid']);
$total_invoices = DB::queryFirstField("SELECT count(*) FROM invoices WHERE status = 'paid'");
$total_invoices_amount = DB::queryFirstField("SELECT sum(amount_btc) FROM invoices WHERE status = 'paid'");
$new_invoices = DB::queryFirstField("SELECT count(*) FROM alerts WHERE type = 'invoice_paid' AND userid = %d", $GLOBALS['userid']);
$new_invoices_amount = DB::queryFirstField("SELECT sum(amount) FROM alerts WHERE type = 'invoice_paid' AND userid = %d", $GLOBALS['userid']);

// Zero needed variables
if ($total_new_deposits == '') { $total_new_deposits = 0; }
if ($total_new_deposits_amount == '') { $total_new_deposits_amount = 0; }
if ($total_users == '') { $total_users = 0; }
if ($new_users == '') { $new_users = 0; }
if ($total_products == '') { $total_products = 0; }
if ($total_products_amount == '') { $total_products_amount = 0; }
if ($new_products == '') { $new_products = 0; }
if ($new_products_amount == '') { $new_products_amount = 0; }
if ($total_invoices == '') { $total_invoices = 0; }
if ($total_invoices_amount == '') { $total_invoices_amount = 0; }
if ($new_invoices == '') { $new_products = 0; }
if ($new_invoices_amount == '') { $new_invoices_amount = 0; }

// Revenue chart
$revenue_labels = array(); $revenue_data = array();
for ($x = 0; $x <= 10; $x++) { 

	// Get date
	$date = $x == 0 ? DB::queryFirstField("SELECT date(now())") : DB::queryFirstField("SELECT date(date_sub(now(), interval $x day))");
	list($year, $month, $day) = explode("-", $date);

	// Get revenuve
	$revenue = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE date(date_added) = '$date'");
	if ($revenue == '') { $revenue = 0; }

	// Add to chart data
	$revenue_labels[] = date('M, d', mktime(0, 0, 0, $month, $day, $year));
	$revenue_data[] = fmoney_coin($revenue);
}

// Template variables
$template->assign('funds_received', fmoney_coin($total_funds_received));
$template->assign('new_deposits', $total_new_deposits);
$template->assign('new_deposits_amount', fmoney_coin($total_new_deposts_amount));
$template->assign('total_users', $total_users);
$template->assign('new_users', $new_users);
$template->assign('total_products', $total_products);
$template->assign('total_products_amount', fmoney_coin($total_products_amount));
$template->assign('new_products', $new_products);
$template->assign('new_products_amount', fmoney_coin($new_products_amount));
$template->assign('total_invoices', $total_invoices);
$template->assign('total_invoices_amount', fmoney_coin($total_invoices_amount));
$template->assign('new_invoices', $new_invoices);
$template->assign('new_invoices_amount', fmoney_coin($new_invoices_amount));

$template->assign('revenue_chart_labels', '"' . implode('","', $revenue_labels) . '"');
$template->assign('revenue_chart_data', implode(", ", $revenue_data));


?>