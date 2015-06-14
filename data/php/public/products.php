<?php

// Initialize
global $template, $config;

// Go through products
$products = array();
$rows = DB::query("SELECT * FROM products WHERE is_enabled = 1 ORDER BY display_name");
foreach ($rows as $row) { 
	if ($row['currency'] == 'fiat') { 
		$row['amount_fiat'] = fmoney($row['amount']);
		$row['amount_btc'] = fmoney_coin($row['amount'] / $config['exchange_rate']);
	} else { 
		$row['amount_fiat'] = fmoney($row['amount'] * $config['exchange_rate']);
		$row['amount_btc'] = fmoney_coin($row['amount']);
	}
	$row['price'] = $row['amount_btc'] . ' BTC (' . $row['amount_fiat'] . ')';
	array_push($products, $row);
}

// Template variables
$template->assign('products', $products);

?>