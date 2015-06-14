<?php

// Initialize
global $template;

// Update general settings
if (isset($_POST['submit']) && $_POST['submit'] == tr('Add New Wallet')) { 

	// Initialize
	$b32 = new bip32();
	$enc_client = new encrypt();
	$required_sigs = $_POST['address_type'] == 'standard' ? 1 : $_POST['multisig_sig_required'];
	$total_sigs = $_POST['address_type'] == 'standard' ? 1 : $_POST['multisig_sig_total'];

	// Validate public keys
	if ($_POST['autogen_keys'] == 0) { 
		for ($x=1; $x <= $total_sigs; $x++) { 
			if (!$import = $b32->import($_POST['bip32_key' . $x])) { $template->add_message("The #$x BIP32 key you specified is an invalid BIP32 key.", 'error'); }
			elseif ($import['type'] != 'public') { $template->add_message("The #$x BIP32 key you specified is an invalid BIP32 key.", 'error'); }
		}
	}

	// Create wallet, if no errors
	if ($template->has_errors != 1) { 

		// Add to DB
		DB::insert('coin_wallets', array(
			'address_type' => $_POST['address_type'], 
			'sigs_required' => $required_sigs, 
			'sigs_total' => $total_sigs, 
			'display_name' => $_POST['wallet_name'])
		);
		$wallet_id = DB::insertId();

		// Gather BIP32 keys
		$keys = array();
		for ($x=1; $x <= $total_sigs; $x++) { 

			// Auto-generate, if needed
			if ($_POST['autogen_keys'] == 1) { 
				$private_key = $b32->generate_master_key();
				$public_key = $b32->extended_private_to_public($private_key);

				array_push($keys, array(
					'num' => $x, 
					'private_key' => $private_key, 
					'public_key' => $public_key)
				);

			} else { $public_key = $_POST['bip32_key' . $x]; }

			// Add key to db
			DB::insert('coin_wallets_keys', array(
				'wallet_id' => $wallet_id, 
				'public_key' => $enc_client->encrypt($public_key))
			);
		}

		// User message
		if ($_POST['autogen_keys'] == 1) { 
			$template = new template('admin/setup/bip32_keys');
			$template->assign('keys', $keys);
			$template->parse(); exit(0);

		} else { 
			$template->add_message("Successfully added new wallet, $_POST[wallet_name]");
		}
	}

// Delete checked wallets
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Delete Checked Wallets')) { 

	// Go through wallets
	$ids = get_chk('wallet_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }

		// Check for unspent inputs
		$count = DB::queryFirstField("SELECT count(*) FROM coin_inputs WHERE wallet_id = %d AND is_spent = 0", $id);
		if ($count > 0) { 
			$template->add_message("Unable to delete wallet ID# $id, as it has unspent inputs.  Please transfer the wallet first via the Financial-&gt;Transfer Wallet menu.", 'error');
		} else { 
			DB::query("DELETE FROM coin_wallets WHERE id = %d", $id);
		}
	}

	// User message
	if ($template->has_errors != 1) { 
		$template->add_message("Successfully deleted all checked wallets.");
	}

// Verify public key
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Verify Public Key')) { 

	// Initialize
	$enc = new encrypt();
	$b32 = new bip32();

	// Get wallet ID
	if (!isset($_POST['verify_wallet_id'])) { 
		$wallet_id = DB::queryFirstField("SELECT id FROM coin_wallets WHERE status = 'active' ORDER BY id LIMIT 0,1");
	} else { $wallet_id = $_POST['verify_wallet_id']; }

	// Gather private keys
	$x=1; $privkeys = array();
	while (1) { 
		$var = 'verify_private_key' . $x;
		if (!isset($_POST[$var])) { break; }
		$privkeys[] = trim($_POST[$var]);
	$x++; }

	// Get public keys
	$rows = DB::query("SELECT * FROM coin_wallets_keys WHERE wallet_id = %d", $wallet_id);
	foreach ($rows as $row) { 
		$public_keys[] = trim($enc->decrypt($row['public_key']));
	}

	// Verify the keys
	$num = 1;
	$ok = true; $no_keys = array();
	foreach ($public_keys as $public_key) { 

		// Go through private keys
		$found = false;
		foreach ($privkeys as $private_key) { 
			if ($public_key == $b32->extended_private_to_public($private_key)) { 
				$found = true;
				break;
			}
		}
		if ($found === false) {
			array_push($no_keys, array('num' => $num, 'public_key' => $public_key));
			$num++;
		}
	}

	// Print response
	if (count($no_keys) > 0) { 
		$template = new template('admin/setup/invalid_bip32_keys');
		$template->assign('keys', $no_keys);
		$template->parse(); exit(0);

	} else { 
		$template->add_message('Successfully verified public keys, and all private keys match appropriately.');
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
	$wallet_javascript .= "wallets['" . $row['id'] . "'] = " . $row['sigs_total'] . ";\n\t";

	// Create BIP32 key fields, if needed
	if ($first === true) { 
		for ($x=1; $x <= $row['sigs_total']; $x++) { 
			$name = $x == 1 ? 'BIP32 Private Key:' : 'BIP32 Private Key ' . $x . ':';
			$bip32_key_fields .= "<tr><td>$name</td><td><textarea name=\"verify_private_key" . $x . "\"></textarea></td></tr>";
		}
		$required_sigs = $row['sigs_total'];
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