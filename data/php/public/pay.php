<?php

// Initialize
global $template, $config;

// Set variables
$amount = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : 0;
$currency = isset($_REQUEST['currency']) ? $_REQUEST['currency'] : 'fiat';
$product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;
$wallet_id = isset($_REQUEST['wallet_id']) ? $_REQUEST['wallet_id'] : 0;
if (isset($_POST['amount_hidden']) && $_POST['amount_hidden'] > 0) { $amount = $_POST['amount_hidden']; }

// Get wallet
if ($wallet_id == 0) { 
	if (!$wallet_id = DB::queryFirstField("SELECT id FROM coin_wallets WHERE status = 'active' ORDER BY id LIMIT 0,1")) {
		trigger_error("Unable to accept payments, because no wallets are currently setup.  Please add a wallet through the Settings-&gt;BIP32 Wallets menu of the admin panel.", E_USER_ERROR);
	}
}

// Determine what amount field to display
if (($amount == 0 && $product_id == 0) && LOGIN === true) { $amount_display = 'form'; }
elseif (($amount == 0 && $product_id == 0) && LOGIN === false) { $amount_display = 'none'; }
elseif ($amount > 0 && LOGIN === false) { $amount_display = 'amount_only'; }
else { $amount_display = 'text'; }

// Create payment session
if ($amount > 0 || $product_id > 0) {
	$client = new transaction();
	$pay_hash = $client->create_pending_session($wallet_id, $product_id, $amount, $currency);
} else { $pay_hash = ''; }

// Registration link vars
$register_vars = 'is_payment=1&currency=' . $currency . '&amount=' . $amount . '&product_id=' . $product_id . '&wallet_id=' . $wallet_id;

// Template variables
$template->assign('amount_raw', $amount);
$template->assign('currency', $currency);
$template->assign('amount_display', $amount_display);
$template->assign('pay_hash', $pay_hash);
$template->assign('product_id', $product_id);
$template->assign('wallet_id', $wallet_id);
$template->assign('register_vars', $register_vars);

?>