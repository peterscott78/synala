<?php

// Initialize
global $template;

// Generate address
if (isset($_POST['submit']) && $_POST['submit'] == tr('Generate Address')) { 

	// Get userid
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", $_POST['gen_username'])) { 
		trigger_error("Username does not exist, $_POST[gen_username]", E_USER_ERROR);
	}

	// Get wallet ID
	if (!isset($_POST['gen_wallet_id'])) { 
		$_POST['gen_wallet_id'] = DB::queryFirstField("SELECT id FROM coin_wallets WHERE status = 'active' ORDER BY id LIMIT 0,1");
	}

	// Generate address
	$b32 = new bip32();
	$address = $b32->generate_address($_POST['gen_wallet_id'], $user_row['id']);

	// User message
	$template->add_message("Successfully generated new address, <b>$address</b>");

}

// Wallet options
$has_multiple_wallets = false; $wallet_options = ''; $first = true;
$rows = DB::query("SELECT * FROM coin_wallets WHERE status = 'active' ORDER BY display_name");
foreach ($rows as $row) { 
	$wallet_options .= "<option value=\"$row[id]\">$row[display_name]";
	if ($first === false) { $has_multiple_wallets = true; }
	$first = false;
}
$username = DB::queryFirstField("SELECT username FROM users WHERE group_id = 1 AND status = 'active' ORDER BY id LIMIT 0,1");

// Template variables
$template->assign('username', $username);
$template->assign('has_multiple_wallets', $has_multiple_wallets);
$template->assign('wallet_options', $wallet_options);

?>