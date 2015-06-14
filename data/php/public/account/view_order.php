<?php

// Initialize
global $template;

// Get order
if (!$row = DB::queryFirstRow("SELECT * FROM orders WHERE id = %d", $_REQUEST['order_id'])) { 
	trigger_error("Order does not exist, ID# $_REQUEST[order_id]", E_USER_ERROR);
}

// Set variables
$_POST['order_id'] = $row['id'];
$row['status'] = ucwords($row['status']);
$row['product_name'] = DB::queryFirstField("SELECT display_name FROM products WHERE id = %d", $row['product_id']) . ' (#' . $row['product_id'] . ')';
$row['amount'] = fmoney_coin($row['amount_btc']) . ' BTC (' . fmoney($row['amount']) . ')';
$row['date_added'] = fdate($row['date_added'], true);

// Template variables
$template->assign('order', $row);

?>