<?php

// Initialize
global $template;

// Send funds, if needed
if (isset($_POST['submit']) && ($_POST['submit'] == tr('Send Funds') || $_POST['submit'] == tr('Sign Online Transaction'))) { 

	// Initialize
	$b32_client = new bip32();
	$send_amount = 0;
	$outputs = array();

	// Sign online transaction
	if ($_POST['submit'] == tr('Sign Online Transaction')) { 

		// Get send
		if (!$send = DB::queryFirstRow("SELECT * FROM coin_sends WHERE id = %d", $_POST['send_id'])) { 
			trigger_error("Send does not exist, ID# $_POST[send_id]", E_USER_ERROR);
		}

		// Get wallet
		if (!$wallet = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $send['wallet_id'])) { 
			trigger_error("Wallet does not exist, ID# $send[wallet_id]", E_USER_ERROR);
		}

		// Get outputs
		$rows = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d ORDER BY id", $_POST['send_id']);
		foreach ($rows as $row) { 
			$send_amount += $row['amount'];
			$outputs[$row['address']] = $row['amount'];
		}

		// Set variables
		$_POST['signing_method'] = 'online';
		$_POST['wallet_id'] = $send['wallet_id'];
		$_POST['note'] = $send['note'];

	// Send funds
	} else { 

		// Gather outputs
		$x=1;
		while (1) { 
			if (!isset($_POST['address' . $x])) { break; }
			if (!isset($_POST['amount' . $x])) { break; }
			if ($_POST['address' . $x] == '') { break; }
			if ($_POST['amount' . $x] == '') { break; }
			if ($_POST['amount' . $x] <= 0) { break; }

			// Validate address
			if (!$b32_client->validate_address($_POST['address'. $x])) { 
				$template->add_message("Invalid payment address, " . $_POST['address' . $x], 'error');
			} else { 
				$outputs[$_POST['address' . $x]] = $_POST['amount' . $x];
				$send_amount += $_POST['amount' . $x];
			}
		$x++; }

		// Check balance
		$balance = $b32_client->get_balance($_POST['wallet_id']);
		if ($send_amount > $balance) { $template->add_message("You do not have enough funds in your wallet to send this transaction.", 'error'); }
	}


	// Process send, if no errors
	if ($template->has_errors != 1) { 

		// Online send
		if ($_POST['signing_method'] == 'online') { 

			// Gather private keys
			$x=1; $privkeys = array();
			while (1) { 
				$var = 'private_key' . $x;
				if (!isset($_POST[$var])) { break; }
				$privkeys[] = trim($_POST[$var]);
			$x++; }

			// Gather inputs
			$client = new rawtx();
			if (!$inputs = $client->gather_inputs($_POST['wallet_id'], $send_amount, $privkeys)) { 
				$template->add_message('Unable to find enough spendable inputs for this transaction.  Please verify public keys via the Settings->Wallets menu.', 'error');
				$template->parse(); exit(0);
			}

			// Create transaction
			$transaction = $client->create_transaction($_POST['wallet_id'], $inputs, $outputs);

			// Sign transaction
			$signed_tx = $client->sign_transaction($transaction, $inputs);
			$txid = bin2hex(strrev(hash('sha256', hash('sha256', hex2bin($signed_tx), true), true)));

			// Mark inputs as locked
			foreach ($inputs as $input) { 
				DB::query("UPDATE coin_inputs SET is_locked = 1 WHERE id = %d", $input['input_id']);
			}

			// Send transaction
			$client = new transaction();
			if (!$client->send_transaction($signed_tx)) { 
				$template->add_message('Unable to send transaction due to an unknown error from bitcoind.', 'error');
				$template->parse(); exit(0);
			}

			// Mark inputs as spent
			foreach ($inputs as $input) { 
				DB::query("UPDATE coin_inputs SET is_spent = 1, is_locked = 0 WHERE id = %d", $input['input_id']);
			}

			// Complete send as needed
			if ($_POST['submit'] == tr('Sign Online Transaction')) { 
				DB::update('coin_sends', array(
					'status' => 'sent', 
					'txid' => $txid), 
				"id = %d", $_POST['send_id']);
				$send_id = $_POST['send_id'];

			} else { 
				$client = new transaction();
				$send_id = $client->add_send($_POST['wallet_id'], 'sent', $_POST['note'], $txid, $outputs);
			}

			// Send notifications
			send_notifications('funds_sent', $send_id);

			// Execute hooks
			execute_hooks('funds_sent', $send_id);

			// User message
			$this->add_message("Successfully processed send and broadcast transaction, TxID $txid");

		// Offline send
		} else { 
			$client = new transaction();
			$client->add_send($_POST['wallet_id'], 'pending', $_POST['note'], '', $outputs);
			$template->add_message("Successfully queued new send.  You may download the appropriate JSON for offline signing via the Pending Sends tab.");
		}

	}

// Download JSON file
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Download JSON File')) { 

	// Initialize
	$bip32 = new bip32();
	$encrypt = new encrypt();

	// Set variables
	$testnet = TESTNET == 1 ? true : false;
	$json = array('testnet' => $testnet, 'inputs' => array(), 'outputs' => array());
	$send_amount = DB::queryFirstField("SELECT sum(amount) FROM coin_sends WHERE status = 'pending'");

	// Get wallet row
	if (!$wrow = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $_POST['pending_wallet_id'])) { 
		trigger_error("Wallet does not exist, ID# $_POST[pending_wallet_id]", E_USER_ERROR);
	}

	// Gather inputs
	$input_amount = 0;
	$rows = DB::query("SELECT * FROM coin_inputs WHERE is_spent = 0 AND is_confirmed = 1 ORDER BY id");
	foreach ($rows as $row) { 
		if ($input_amount >= $send_amount) { break; }

		// Get address row
		if (!$addr_row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $row['address'])) {
			continue;
		}

		// Get keyindexes
		if ($wrow['address_type'] == 'multisig') { 

			$keyindexes = array(); $public_keys = array();
			$addr_rows = DB::query("SELECT * FROM coin_addresses_multisig WHERE address = %s ORDER BY id", $row['address']);
			foreach ($addr_rows as $arow) { 
				$keyindexes[] = $addr_row['is_change_address'] . '/' . $arow['address_num'];

				// Get public key
				$ext_public_key = trim($encrypt->decrypt(DB::queryFirstField("SELECT public_key FROM coin_wallets_keys WHERE id = %d", $arow['key_id'])));
				$child_key = $bip32->build_key($ext_public_key, $addr_row['is_change_address'] . '/' . $arow['address_num'])[0];
				$import = $bip32->import($child_key);
				$public_keys[] = $import['key'];
			}
			$sigscript = $bip32->create_redeem_script($wrow['sigs_required'], $public_keys);

		} else { 
			$keyindexes = $addr_row['is_change_address'] . '/' . $addr_row['address_num'];
			$decode_address = $bip32->base58_decode($row['address']);
			$sigscript = '76a914' . substr($decode_address, 2, 40) . '88ac';
		}

		// Set vars
		$vars = array(
			'input_id' => $row['id'], 
			'amount' => $row['amount'], 
			'txid' => $row['txid'], 
			'vout' => $row['vout'], 
			'sigscript' => $sigscript, 
			'keyindex' => $keyindexes
		);
		array_push($json['inputs'], $vars);
	}

	// Gather outputs
	$rows = DB::query("SELECT * FROM coin_sends WHERE status = 'pending' ORDER BY id");
	foreach ($rows as $row) { 

		// Gather recipients
		$recipients = array();
		$arows = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d", $row['id']);
		foreach ($arows as $arow) { 
			$vars = array(
				'amount' => $arow['amount'], 
				'address' => $arow['address']
			);
			array_push($recipients, $vars);
		}

		// Get change address
		$change_address = $bip32->generate_address($wrow['id'], 0, 1);
		$change_sigscript = $bip32->address_to_sigscript($change_address);

		// Get key indexes of change address
		$change_keyindexes = array();
		$addr_rows = DB::query("SELECT * FROM coin_addresses_multisig WHERE address = %s", $change_address);
		foreach ($addr_rows as $addr_row) { 
			$change_keyindexes[] = '1/' . $addr_row['address_num'];
		}

		// Set vars
		$vars = array(
			'output_id' => $arow['id'], 
			'recipients' => $recipients, 
			'change_keyindex' => $change_keyindexes, 
			'change_sigscript' => $change_sigscript
		);
		array_push($json['outputs'], $vars);
	}

	// Send file
	header("Content-disposition: attachment; filename=\"tx.json\"");
	header("Content-type: text/json");
	echo json_encode($json);
	exit(0);

// Delete checked sends
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Delete Checked Sends')) { 

	// Delete
	$ids = get_chk('send_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("DELETE FROM coin_sends WHERE id = %d", $id);
	}

	// User message
	$template->add_message("Successfully deleted checked pending sends.");

// Upload signed sends
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Upload Signed Sends')) { 

	// Load JSON file
	try {
		$json = json_decode(file_get_contents($_FILES['signed_json']['tmp_name']), true);
	} catch (Exception $e) { 
		trigger_error("You did not upload a properly formatted JSON file.  Please check the file you uploaded, and try again.");
	}
	@unlink($_FILES['signed_json']['tmp_name']);

	// Mark inputs as locked
	foreach ($json['spent_inputs'] as $vars) { 
		DB::query("UPDATE coin_inputs SET is_locked = 1 WHERE txid = %s AND vout = %d", $vars['txid'], $vars['vout']);
	}

	// Go through txs
	$client = new transaction();
	foreach ($json['tx'] as $tx) { 

		// Send transaction
		if (!$client->send_transaction($tx['hexcode'])) { 
			$template->add_message("Unable to send transaction ID# $tx[txid] due to an unknown error from bitcoind.", 'error');
			continue;
		}

		// Update sent
		DB::query("UPDATE coin_sends SET status = 'sent', txid = %s WHERE id = %d", $tx['txid'], $tx['output_id']);

		// Send notifications
		send_notifications('funds_sent', $tx['output_id']);

		// Execute hooks
		execute_hooks('funds_sent', $tx['output_id']);
	}

	// Go through change inputs
	foreach ($json['change_inputs'] as $vars) { 
		$client->add_input($vars['address'], $vars['amount'], $vars['txid'], $vars['vout']);
	}

	// Go through spent inputs
	foreach ($json['spent_inputs'] as $vars) { 
		DB::query("UPDATE coin_inputs SET is_spent = 1 WHERE txid = %s AND vout = %d", $vars['txid'], $vars['vout']);
	}

	// User message
	if ($template->has_errors != 1) { 
		$template->add_message("Successfully broadcast all transactions to the blockchain.");
	}

// Broadcast transaction
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Broadcast Transaction')) { 

	// Set variables
	$signed_tx = trim($_POST['signed_hex']);
	$txid = bin2hex(strrev(hash('sha256', hash('sha256', hex2bin($signed_tx), true), true)));

	// Mark inputs as locked
	$input_ids = explode(",", $_POST['input_ids']);
	foreach ($input_ids as $input_id) { 
		if (!$input_id > 0) { continue; }
		DB::query("UPDATE coin_inputs SET is_locked = 1 WHERE id = %d", $input_id);
	}

	// Send transaction
	$client = new transaction();
	if (!$client->send_transaction($signed_tx)) { 
		$template->add_message("Unable to send transaction ID# $tx[txid] due to an unknown error from bitcoind.", 'error');
		continue;
	}

	// Process transaction, if ok
	if ($template->has_errors != 1) { 

		// Update db
		DB::update('coin_sends', array(
			'status' => 'sent', 
			'txid' => $txid), 
		"id = %d", $_POST['send_id']);

		// Mark inputs as spent
		$input_ids = explode(",", $_POST['input_ids']);
		foreach ($input_ids as $input_id) { 
			if (!$input_id > 0) { continue; } 
			DB::query("UPDATE coin_inputs SET is_spent = 1, is_locked = 0 WHERE id = %d", $input_id);
		}

		// Send notifications
		send_notifications('funds_sent', $_POST['send_id']);

		// Execute hooks
		execute_hooks('funds_sent', $_POST['send_id']);

		// User message
		$template->add_message("Successfully broadcast transaction, TxID $txid");
	}

}

// Initialize
$bip32 = new bip32();

// Get wallets
$first = true; $bip32_key_fields = ''; $required_sigs = 0;
$wallet_id = 0; $wallet_javascript = ''; $wallet_options = '';
$rows = DB::query("SELECT * FROM coin_wallets WHERE status = 'active' ORDER BY display_name");
foreach ($rows as $row) { 
	$wallet_id = $row['id'];
	$balance = $bip32->get_balance($row['id']);
	$wallet_options .= "<option value=\"$row[id]\">$row[display_name] ($balance BTC)";
	$wallet_javascript .= "wallets['" . $row['id'] . "'] = " . $row['sigs_required'] . ";\n\t";

	// Create BIP32 key fields, if needed
	if ($first === true) { 
		for ($x=1; $x <= $row['sigs_required']; $x++) { 
			$name = $x == 1 ? 'BIP32 Private Key:' : 'BIP32 Private Key ' . $x . ':';
			$bip32_key_fields .= "<tr><td>$name</td><td><textarea name=\"private_key" . $x . "\"></textarea></td></tr>";
		}
		$required_sigs = $row['sigs_required'];
		$first = false;
	}
}

// Template variables
$template->assign('balance', $balance);
$template->assign('has_multiple_wallets', (count($rows) > 1 ? true : false));
$template->assign('wallet_id', $wallet_id);
$template->assign('wallet_options', $wallet_options);
$template->assign('wallet_javascript', $wallet_javascript);
$template->assign('bip32_key_fields', $bip32_key_fields);
$template->assign('required_sigs', $required_sigs);

?>