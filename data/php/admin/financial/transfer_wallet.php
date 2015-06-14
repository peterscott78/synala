<?php

// Initialize
global $template, $config;
$bip32 = new bip32();

// Transfer wallet
if (isset($_POST['submit']) && $_POST['submit'] == tr('Transfer Wallet')) { 

	// Initialize
	$enc_client = new encrypt();

	// Get wallet
	if (!$wrow = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $_POST['wallet_id'])) { 
		trigger_error("Wallet does not exist, ID# $wallet_id", E_USER_ERROR);
	}

	// Add new wallet to DB
	DB::insert('coin_wallets', array(
		'address_type' => $wrow['address_type'], 
		'sigs_required' => $wrow['sigs_required'], 
		'sigs_total' => $wrow['sigs_total'], 
		'display_name' => $wrow['display_name'])
	);
	$new_wallet_id = DB::insertId();

	// Gather BIP32 keys
	for ($x=1; $x <= $wrow['sigs_total']; $x++) { 
		$public_key = $enc_client->encrypt($_POST['public_key' . $x]);
		DB::insert('coin_wallets_keys', array(
			'wallet_id' => $new_wallet_id, 
			'public_key' => $public_key)
		);
	}

	// Gather private keys
	$x=1; $privkeys = array();
	while (1) { 
		$var = 'private_key' . $x;
		if (!isset($_POST[$var])) { break; }
		$privkeys[] = $_POST[$var];
	$x++; }

	// Get total inputs
	$balance = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE wallet_id = %d AND is_spent = 0", $_POST['wallet_id']);
	$total_inputs = DB::queryFirstField("SELECT count(*) FROM coin_inputs WHERE wallet_id = %d AND is_spent = 0", $_POST['wallet_id']);
	$balance -= ($total_inputs * $config['btc_txfee']);

	// Generate address from new wallet
	$address = $bip32->generate_address($new_wallet_id);
	$outputs = array($address => $balance);

	// Gather all unspent inputs
	$client = new rawtx();
	$inputs = $client->gather_inputs($_POST['wallet_id'], $balance, $privkeys);

	// Create transaction
	$transaction = $client->create_transaction($_POST['wallet_id'], $inputs, $outputs);

	// Sign transaction
	$signed_tx = $client->sign_transaction($transaction, $inputs);

	// Send transaction
	$client = new transaction();
	$client->send_transaction($signed_tx);

	// Update wallets
	DB::query("UPDATE coin_wallets SET status = 'inactive' WHERE id = %d", $_POST['wallet_id']);
	DB::query("UPDATE coin_inputs SET is_spent = 1 WHERE wallet_id = %d", $_POST['wallet_id']);

	// User message
	$balance = fmoney_coin($balance);
	$template->add_message("Successfully transferred your wallet to the new BIP32 keys.  A total of $balance BTC was transferred to your new BIP32 key(s), and your new wallet ID# is $new_wallet_id.");

}

// Get wallets
$first = true; $bip32_key_fields = ''; $bip32_public_key_fields = ''; $required_sigs = 0; $total_sigs = 0;
$wallet_id = 0; $wallet_javascript = ''; $wallet_totals_javascript = ''; $wallet_options = '';
$rows = DB::query("SELECT * FROM coin_wallets WHERE status = 'active' ORDER BY display_name");
foreach ($rows as $row) { 
	$wallet_id = $row['id'];
	$balance = $bip32->get_balance($row['id']);
	$wallet_options .= "<option value=\"$row[id]\">$row[display_name] ($balance BTC)";
	$wallet_javascript .= "wallets['" . $row['id'] . "'] = " . $row['sigs_required'] . ";\n\t";
	$wallet_totals_javascript .= "wallet_totals['" . $row['id'] . "'] = " . $row['sigs_total'] . ";\n\t";

	// Create BIP32 key fields, if needed
	if ($first === true) { 
		for ($x=1; $x <= $row['sigs_required']; $x++) { 
			$name = $x == 1 ? 'BIP32 Private Key:' : 'BIP32 Private Key ' . $x . ':';
			$bip32_key_fields .= "<tr><td>$name</td><td><textarea name=\"private_key" . $x . "\"></textarea></td></tr>";
		}

		for ($x=1; $x <= $row['sigs_total']; $x++) { 
			$public_name = $x == 1 ? 'BIP32 Public Key:' : 'BIP32 Public Key ' . $x . ':';
			$bip32_public_key_fields .= "<tr><td>$public_name</td><td><textarea name=\"public_key" . $x . "\"></textarea></td></tr>";
		}

		$required_sigs = $row['sigs_required'];
		$total_sigs = $row['sigs_total'];
		$first = false;
	}
}

// Template variables
$template->assign('balance', $balance);
$template->assign('has_multiple_wallets', (count($rows) > 1 ? true : false));
$template->assign('wallet_id', $wallet_id);
$template->assign('wallet_options', $wallet_options);
$template->assign('wallet_javascript', $wallet_javascript);
$template->assign('wallet_totals_javascript', $wallet_totals_javascript);
$template->assign('bip32_key_fields', $bip32_key_fields);
$template->assign('bip32_public_key_fields', $bip32_public_key_fields);
$template->assign('required_sigs', $required_sigs);
$template->assign('total_sigs', $total_sigs);

?>