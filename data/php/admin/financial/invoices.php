<?php

// Initialize
global $template, $config;
$bip32 = new bip32();

// Generate invoice
if (isset($_POST['submit']) && $_POST['submit'] == tr('Generate Invoice')) { 

	// Get userid
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", $_POST['username'])) { 
		$template->add_message("Username does not exist, $_POST[username]", 'error');
	}

	// Perform checks
	if ($_POST['amount'] == '') { $template->add_message('You did not specify an amount', 'error'); }
	elseif (!is_numeric($_POST['amount'])) { $template->add_message('Invalid amount specified, ' . $_POST['amount'], 'error'); }
	elseif (!$_POST['amount'] > 0) { $template->add_message('Amount must be greater than 0.', 'error'); }

	// Add invoice, if no errors
	if ($template->has_errors != 1) { 

		// Get amounts
		if ($_POST['currency'] == 'fiat') { 
			$amount = $_POST['amount'];
			$amount_btc = ($amount / $config['exchange_rate']);
		} else { 
			$amount_btc = $_POST['amount'];
			$amount = ($amount_btc * $config['exchange_rate']);
		}

		// Generate payment address
		$address = $bip32->generate_address($_POST['wallet_id'], $user_row['id']);
		DB::query("UPDATE coin_addresses SET is_used = 1 WHERE address = %s", $address);

		// Add new invoice
		DB::insert('invoices', array(
			'wallet_id' => $_POST['wallet_id'], 
			'userid' => $user_row['id'], 
			'currency' => $_POST['currency'], 
			'amount' => $amount, 
			'amount_btc' => $amount_btc, 
			'payment_address' => $address, 
			'note' => $_POST['note'], 
			'process_note' => '')
		);
		$invoice_id = DB::insertId();

		// Send notifications
		send_notifications('invoice_created', $invoice_id);

		// User message
		$template->add_message("Successfully generated a new pending invoice for user, $_POST[username]");
	}

// Process invoices
} elseif (isset($_POST['submit']) && $_POST['submit'] == 'Process Checked Invoices') { 

	// Process
	$ids = get_chk('invoice_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::update('invoices', array(
			'status' => $_POST['status'], 
			'date_paid' => DB::sqleval('now()'), 
			'process_note' => $_POST['note']), 
		"id = %d", $id);
	}

	// User message
	$template->add_message("Successfully processed all checked invoices, and marked them as <b>$_POST[status]</b>.");

// Update invoice details
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Update Invoice Details')) { 

	// Get userid
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", $_POST['username'])) { 
		$template->add_message("Username does not exist, $_POST[username]", 'error');
	}

	// Perform checks
	if ($_POST['amount'] == '') { $template->add_message('You did not specify an amount', 'error'); }
	elseif (!is_numeric($_POST['amount'])) { $template->add_message('Invalid amount specified, ' . $_POST['amount'], 'error'); }
	elseif (!$_POST['amount'] > 0) { $template->add_message('Amount must be greater than 0.', 'error'); }

	// Add invoice, if no errors
	if ($template->has_errors != 1) { 

		// Get amounts
		if ($_POST['currency'] == 'fiat') { 
			$amount = $_POST['amount'];
			$amount_btc = ($amount / $config['exchange_rate']);
		} else { 
			$amount_btc = $_POST['amount'];
			$amount = ($amount_btc * $config['exchange_rate']);
		}

		// Update db
		DB::update('invoices', array(
			'userid' => $user_row['id'], 
			'status' => $_POST['status'], 
			'currency' => $_POST['currency'], 
			'amount' => $amount, 
			'amount_btc' => $amount_btc, 
			'note' => $_POST['note'], 
			'process_note' => $_POST['process_note']), 
		"id = %d", $_POST['invoice_id']);

		// User message
		$this->add_message("Successfully updated details for invoice ID# $_POST[invoice_id]");
	}

}

// Get wallets
$wallet_id = 0; $wallet_options = ''; $balance = 0;
$rows = DB::query("SELECT * FROM coin_wallets WHERE status = 'active' ORDER BY display_name");
foreach ($rows as $row) { 
	$wallet_id = $row['id'];
	$balance = $bip32->get_balance($row['id']);
	$wallet_options .= "<option value=\"$row[id]\">$row[display_name] ($balance BTC)";
}

// Template variables
$template->assign('balance', $balance);
$template->assign('has_multiple_wallets', (count($rows) > 1 ? true : false));
$template->assign('wallet_id', $wallet_id);
$template->assign('wallet_options', $wallet_options);

?>