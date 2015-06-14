<?php

// Initialize
global $template;

// Check for pending items
$order_count = DB::queryFirstField("SELECT count(*) FROM orders WHERE userid = %d", $GLOBALS['userid']);
$invoice_count = DB::queryFirstField("SELECT count(*) FROM invoices WHERE userid = %d", $GLOBALS['userid']);

// Template variables
$_POST['userid'] = $GLOBALS['userid'];
$template->assign('has_orders', ($order_count > 0 ? true : false));
$template->assign('has_invoices', ($invoice_count > 0 ? true : false));

?>