<?php

// Initialize
global $template, $config;

// Get send
if (!$send = DB::queryFirstRow("SELECT * FROM coin_sends WHERE id = %d", $_REQUEST['send_id'])) { 
	trigger_error("Send does not exist in database, ID# $_REQUEST[send_id]", E_USER_ERROR);
}
if ($send['status'] != 'pending') { trigger_error("Send is not in pending status, hence can not be signed.", E_USER_ERROR); }

// Get wallet
if (!$wallet = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $send['wallet_id'])) { 
	trigger_error("Wallet does not exist, ID# $send[wallet_id]", E_USER_ERROR);
}

// Initialize
$bip32 = new bip32();
$enc = new encrypt();

// Get sigs required
$sigs_required = array();
for ($x=1; $x <= $wallet['sigs_required']; $x++) {
	array_push($sigs_required, array('num' => $x));
}

// Gather outputs
$outputs = array(); $send_amount = 0;
$rows = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d ORDER BY id", $send['id']);
foreach ($rows as $row) {
	$outputs[$row['address']] = $row['amount'];
	$send_amount += $row['amount'];
}

// Gather inputs
$inputs = array(); $input_amount = 0; $input_ids = array();
$rows = DB::query("SELECT * FROM coin_inputs WHERE is_spent = 0 AND wallet_id = %d ORDER BY id", $send['wallet_id']);
foreach ($rows as $row) { 
	$send_amount += $config['btc_txfee'];

	// Create sigscript
	if ($wallet['address_type'] == 'multisig') { 

		// Set variables
		$is_change = DB::queryFirstField("SELECT is_change_address FROM coin_addresses WHERE address = %s", $row['address']);

		// Get addresses
		$public_keys = array(); $keyindexes = array();
		$addr_rows = DB::query("SELECT * FROM coin_addresses_multisig WHERE address = %s ORDER BY id", $row['address']);
		foreach ($addr_rows as $addr_row) { 

			// Get public key & index
			$public_key = trim($enc->decrypt(DB::queryFirstField("SELECT public_key FROM coin_wallets_keys WHERE id = %d", $addr_row['key_id'])));
			$keyindex = $is_change . '/' . $addr_row['address_num'];
			$keyindexes[] = $keyindex;

			// Generate child key
			$child_ext_key = $bip32->build_key($public_key, $keyindex)[0];
			$public_keys[] = $bip32->import($child_ext_key)['key'];
		}
		$scriptsig = $bip32->create_redeem_script($wallet['sigs_required'], $public_keys);
		$keyindex = implode(", ", $keyindexes);

	} else { 

		$addr_row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $row['address']);
		$keyindex = $addr_row['is_change_address'] . '/' . $addr_row['address_num'];

		$decode_address = $bip32->base58_decode($row['address']);
		$scriptsig = '76a914' . substr($decode_address, 2, 40) . '88ac';
	}

	// Add input
	$vars = array(
		'input_id' => $row['id'], 
		'txid' => $row['txid'], 
		'vout' => $row['vout'], 
		'amount' => $row['amount'], 
		'keyindex' => $keyindex, 
		'scriptsig' => $scriptsig
	);
	array_push($inputs, $vars);
	$input_ids[] = $row['id'];

}

// Create transaction
$client = new rawtx();
$trans = $client->create_transaction($send['wallet_id'], $inputs, $outputs);

// Template variables
$template->assign('send_id', $_REQUEST['send_id']);
$template->assign('sigs_required', $sigs_required);
$template->assign('hexcode', implode("", $trans));
$template->assign('inputs', $inputs);
$template->assign('input_ids', implode(",", $input_ids));

?>