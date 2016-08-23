<?php

class transaction {

//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct() { 

	// Initialize
	global $config;
	include_once(SITE_PATH . '/data/lib/jsonRPCClient.php');

	// Init RPC client
	$rpc_url = 'http://' . $config['btc_rpc_user'] . ':' . $config['btc_rpc_pass'] . '@' . $config['btc_rpc_host'] . ':' . $config['btc_rpc_port'];
	$this->client = new jsonRPCClient($rpc_url);

}

//////////////////////////////////////////////////////////////////////////
// Add pending session
//////////////////////////////////////////////////////////////////////////

public function create_pending_session($wallet_id, $product_id = 0, $amount = 0, $currency = 'btc') { 

	// Initialize
	global $config, $template;
	$userid = LOGIN === true ? $GLOBALS['userid'] : 0;
	$expire_time = time() + $config['payment_expire_seconds'];

	// Get hash
	do {
		$hash = generate_random_string(120);
		if ($row = DB::queryFirstRow("SELECT * FROM coin_pending_payment WHERE pay_hash = %s", hash('sha512', 120))) { 
			$exists = 1;
		} else { $exists = 0; }
	} while ($exists > 0);

	// Get product, if needed
	if ($product_id > 0) { 
		if (!$prow = DB::queryFirstRow("SELECT * FROM products WHERE id = %d", $product_id)) { 
			trigger_error("Product does not exist, ID# $product_id", E_USER_ERROR);
		}
		$amount = $prow['amount'];
		$currency = $prow['currency'];
		$item_name = $prow['display_name'];
	} else { $item_name = ''; }

	// Get amount
	if ($currency == 'fiat') { 
		$amount_btc = ($amount / $config['exchange_rate']);
	} else { 
		$amount_btc = $amount;
		$amount = ($amount_btc * $config['exchange_rate']);
	}

	// Get payment address
	if ($userid > 0) { 
		$client = new bip32();
		$payment_address = $client->get_user_address($wallet_id, $userid);

		// Delete any existing pending payments
		DB::query("DELETE FROM coin_pending_payment WHERE payment_address = %s AND status = 'pending'", $payment_address);

	} else { $payment_address = ''; }

	// Add to db
	DB::insert('coin_pending_payment', array(
		'wallet_id' => $wallet_id, 
		'pay_hash' => $hash, 
		'userid' => $userid, 
		'item_id' => $product_id, 
		'amount' => $amount, 
		'amount_btc' => $amount_btc, 
		'expire_time' => $expire_time, 
		'payment_address' => $payment_address)
	);

	// Template variables
	$template->assign('payment_address', $payment_address);
	$template->assign('currency', $currency);
	$template->assign('amount', fmoney_coin($amount_btc));
	$template->assign('amount_fiat', fmoney($amount));
	$template->assign('product_id', $product_id);
	$template->assign('product_name', $item_name);

	// Return hash
	return $hash;

}

//////////////////////////////////////////////////////////////////////////
// Add send
//////////////////////////////////////////////////////////////////////////

public function add_send($wallet_id, $status = 'pending', $note = '', $txid = '', $outputs = array()) { 

	// Initialize
	$total_amount = array_sum(array_values($outputs));

	// Add pending send
	DB::insert('coin_sends', array(
		'wallet_id' => $wallet_id, 
		'status' => $status, 
		'amount' => $total_amount, 
		'txid' => $txid, 
		'note' => $note)
	);
	$send_id = DB::insertId();

	// Add outputs
	foreach ($outputs as $address => $amount) { 
		DB::insert('coin_sends_addresses', array(
			'send_id' => $send_id, 
			'amount' => $amount, 
			'address' => $address)
		);
	}

	// Return
	return $send_id;

}

//////////////////////////////////////////////////////////////////////////
// Check mempool
//////////////////////////////////////////////////////////////////////////

public function get_info() { 

	// Get block
	try {
		$vars = $this->client->getinfo();
	} catch (Exception $e) { return false; }

	// Return
	return $vars;

}

//////////////////////////////////////////////////////////////////////////
// Check mempool
//////////////////////////////////////////////////////////////////////////

public function check_mempool() { 

	// Get current & new mempools
	$current_mempool = DB::queryFirstColumn("SELECT txid FROM coin_mempool");
	$mempool = $this->client->getrawmempool();

	// Go through mempool
	foreach ($mempool as $txid) { 
		if ($txid == '') { continue; }
		if (in_array($txid, $current_mempool)) { continue; }

		// Check transaction
		$this->check_transaction($txid);

		// Add to mempool
		DB::insert('coin_mempool', array('txid' => $txid));
	}

}

//////////////////////////////////////////////////////////////////////////
// Check block
//////////////////////////////////////////////////////////////////////////

public function check_block() { 

	// Initialize
	global $config;
	if (!isset($config['blocknum'])) { return; }

	// Get current block num
	try {
		$vars = $this->client->getinfo();
	} catch (Exception $e) { return false; }

	// Check for 0 block
	$blocknum = $vars['blocks'];
	if ($config['blocknum'] == 0) { 
		update_config_var('blocknum', $blocknum);
		return;
	}

	// Process blocks
	while ($blocknum > $config['blocknum']) { 
		$block_hash = $this->client->getblockhash((int) $crow['blocknum']);
		$this->process_block($block_hash);
		
		$config['blocknum']++;
		update_config_var('blocknum', $config['blocknum']);
	}


}

//////////////////////////////////////////////////////////////////////////
// Process block
//////////////////////////////////////////////////////////////////////////

public function process_block($block_hash) { 

	// Get block
	try {
		$vars = $this->client->getblock($block_hash);
	} catch (Exception $e) { return false; }

	// Add confirmation
	DB::query("UPDATE coin_inputs SET confirmations = confirmations + 1");
	//DB::query("UPDATE coin_transactions SET confirmations = confirmations + 1 WHERE currency = %s", $this->currency);

	// Set variables
	$blocknum = $vars['height'];
	$tx = isset($vars['tx']) && is_array($vars['tx']) ? $vars['tx'] : array();
	$mempool = DB::queryFirstColumn("SELECT txid FROM coin_mempool");

	// Go through txs
	foreach ($tx as $txid) { 
		if (!in_array($txid, $mempool)) { $this->check_transaction($txid); }
		DB::query("UPDATE coin_inputs SET confirmations = 1, blocknum = %d WHERE txid = %s", $blocknum, $txid);
		DB::query("DELETE FROM coin_mempool WHERE txid = %s", $txid);
	}

	// Check unconfirmed
	$this->check_unconfirmed();

}

//////////////////////////////////////////////////////////////////////////
// Check single transaction
//////////////////////////////////////////////////////////////////////////

public function check_transaction($txid) { 

	// Get transaction
	try {
		$trans = $this->client->getrawtransaction($txid, 1);
	} catch (Exception $e) { return false; }

	// Initial checks
	if (!isset($trans['vout'])) { return false; }
	if (!is_array($trans['vout'])) { return false; }

	// Go through outputs
	foreach ($trans['vout'] as $output) { 
			
		// Initial checks
		if (!isset($output['scriptPubKey'])) { continue; }
		if (!isset($output['scriptPubKey']['addresses'])) { continue; }
		if (!isset($output['scriptPubKey']['addresses'][0])) { continue; }

		// Check address
		$address = $output['scriptPubKey']['addresses'][0];
		if (!$row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $address)) { continue; }

		// Process deposit
		$this->add_input($address, $output['value'], $txid, $output['n'], $output['scriptPubKey']['hex']);
	}

	// Go through inputs
	foreach ($trans['vin'] as $input) { 
		if (!isset($input['vout'])) { continue; }

		// Check for unspent input
		if (!$row = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE txid = %s AND vout = %d AND is_spent = 0 AND is_locked = 0", $input['txid'], $input['vout'])) { 
			continue;
		}

		// Add unauthorized send
		DB::insert('coin_unauthorized_sends', array(
			'input_id' => $row['id'], 
			'txid' => $trans['txid'])
		);
		DB::query("UPDATE coin_inputs SET is_spent = 1 WHERE id = %d", $row['id']);
	}

}

//////////////////////////////////////////////////////////////////////////
// Get transaction
//////////////////////////////////////////////////////////////////////////

public function get_tx($txid) { 

	// Get transaction
	try {
		$trans = $this->client->getrawtransaction($txid, 1);
	} catch (Exception $e) { return false; }

	// Check for blockhash
	if (isset($trans['blockhash'])) { 
		$block = $this->client->getblock($trans['blockhash']);
		$trans['blocknum'] = $block['height'];
	}

	// Go through inputs
	$trans['inputs'] = array();
	$trans['input_amount'] = 0;
	foreach ($trans['vin'] as $input) { 

		// Get input transaction
		try {
			$input_trans = $this->client->getrawtransaction($input['txid'], 1);
		} catch (Exception $e) { return false; }


		// Set input vars
		$vars = array(
			'txid' => $input['txid'], 
			'vout' => $input['vout'], 
			'scriptsig' => ''
		);

		// Format script sig
		while (strlen($input['scriptSig']['hex']) > 80) { 
			$temp = substr($input['scriptSig']['hex'], 0, 80);
			$input['scriptSig']['hex'] = preg_replace("/^$temp/", "", $input['scriptSig']['hex']);
			$vars['scriptsig'] .= $temp . "<br>";
		}

		// Get amount
		if (isset($input_trans['vout'][$input['vout']])) {
			$vars['amount'] = $input_trans['vout'][$input['vout']]['value'];
			$trans['input_amount'] += $vars['amount'];
		}

		// Add input
		array_push($trans['inputs'], $vars);
	}

	// Go through outputs
	$trans['outputs'] = array();
	$trans['output_amount'] = 0;
	foreach ($trans['vout'] as $output) { 
		$trans['output_amount'] += $output['value'];

		$vars = array(
			'amount' => $output['value'], 
			'address' => $output['scriptPubKey']['addresses'][0], 
			'scriptsig' => $output['scriptPubKey']['asm']
		);
		array_push($trans['outputs'], $vars);
	}

	// Format amounts
	$trans['fees'] = fmoney_coin($trans['input_amount'] - $trans['output_amount']);
	$trans['input_amount'] = fmoney_coin($trans['input_amount']);
	$trans['output_amount'] = fmoney_coin($trans['output_amount']);

	// Return
	return $trans;

}

//////////////////////////////////////////////////////////////////////////
// Add input
//////////////////////////////////////////////////////////////////////////

public function add_input($address, $amount, $txid, $vout, $scriptsig = '', $confirmations = 0, $blocknum = 0) {  

	// Initialize
	global $config;

	// Check mempool
	if ($row = DB::queryFirstRow("SELECT * FROM coin_mempool WHERE txid = %s AND vout = %d", $txid, $vout)) { 
		return false;
	}
	DB::insert('coin_mempool', array('txid' => $txid, 'vout' => $vout));

	// Get address
	if (!$addr_row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $address)) { return false; }
	$is_confirmed = ($confirmations >= $config['btc_minconf'] || $addr_row['is_change_address'] == 1) ? 1 : 0;

	// Check for invoice
	$product_id = 0; $invoice_id = 0; $order_id = 0; $order_complete = false; $overpayment = 0;
	if ($irow = DB::queryFirstRow("SELECT * FROM invoices WHERE payment_address = %s", $address)) { 
		$invoice_id = $irow['id'];

	// Check for order
	} elseif ($prow = DB::queryFirstRow("SELECT * FROM coin_pending_payment WHERE payment_address = %s", $address)) { 
		$prow['amount_received'] += $amount;
		if ($prow['amount_received'] >= $prow['amount_btc']) { 
			DB::query("UPDATE coin_pending_payment SET status = 'approved', amount_received = amount_received + %d WHERE id = %d", $amount, $prow['id']);
			if ($prow['item_id'] > 0) { $order_complete = true; }
		} else { 
			DB::query("UPDATE coin_pending_payment SET amount_received = amount_received + %d WHERE id = %d", $amount, $prow['id']);			
		}
		$product_id = $prow['item_id'];
		if ($prow['amount_received'] > $prow['amount_btc']) { $overpayment = ($prow['amount_received'] - $prow['amount_btc']); }
	}

	// Check if exists
	if ($row = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE txid = %s AND vout = %d", $txid, $vout)) { 
		return false;
	}

	// Update invoice, if needed
	if ($invoice_id > 0) { 
		$irow['amount_paid'] += $amount;
		$updates = array('amount_paid' => $irow['amount_paid']);
		if ($irow['amount_paid'] >= $irow['amount_btc']) { 
			$updates['status'] = 'paid';
			$updates['date_paid'] = DB::sqleval('now()');
			DB::update('invoices', $updates, "id = %d", $invoice_id);
			if ($irow['amount_paid'] > $irow['amount_btc']) { $overpayment = ($irow['amount_paid'] - $irow['amount_btc']); }
		}

	// Add order, if needed
	} elseif ($order_complete === true) { 
		DB::insert('orders', array(
			'userid' => $addr_row['userid'], 
			'product_id' => $product_id, 
			'amount' => $prow['amount'], 
			'amount_btc' => $prow['amount_btc'])
		);
		$order_id = DB::insertId();
	}

	// Add to DB
	$hash = $txid . ':' . $vout;
	DB::insert('coin_inputs', array(
		'userid' => $addr_row['userid'], 
		'wallet_id' => $addr_row['wallet_id'], 
		'product_id' => $product_id, 
		'order_id' => $order_id, 
		'invoice_id' => $invoice_id, 
		'is_confirmed' => $is_confirmed, 
		'is_change' => $addr_row['is_change_address'], 
		'confirmations' => $confirmations, 
		'blocknum' => $blocknum, 
		'address' => $address, 
		'txid' => $txid, 
		'vout' => $vout, 
		'amount' => $amount, 
		'hash' => $hash)
	);
	$input_id = DB::insertId();

	// Mark address as used
	DB::query("UPDATE coin_addresses SET is_used = 1, total_input = total_input + %d WHERE address = %s", $amount, $address);

	// Add overpayment, if needed
	if ($overpayment != 0) { 
		DB::insert('coin_overpayments', array(
			'userid' => $addr_row['userid'], 
			'input_id' => $input_id, 
			'amount_btc' => $overpayment
		));
	}

	// Add alerts
	if ($product_id > 0) { add_alert('product_purchase', $input_id, $amount); }
	elseif ($invoice_id > 0) { add_alert('invoice_paid', $input_id, $amount); }
	elseif ($addr_row['is_change_address'] != 1) { add_alert('new_deposit', $input_id, $amount); }

	// Process notifications
	if ($addr_row['is_change_address'] != 1) { send_notifications('new_deposit', $input_id); }
	if ($product_id > 0) { send_notifications('product_purchase', $input_id); }
	if ($invoice_id > 0) { send_notifications('invoice_paid', $input_id); }

	// Execute hooks, as needed
	if ($addr_row['is_change_address'] != 1) { execute_hooks('new_deposit', $input_id); }
	if ($is_confirmed == 1) { execute_hooks('confirmed_deposit', $input_id); }
	if ($product_id > 0) { execute_hooks('product_purchased', $input_id, $product_id); }
	if ($invoice_id > 0) { execute_hooks('invoice_paid', $input_id); }

	// Return
	return $input_id;
}

//////////////////////////////////////////////////////////////////////////
// Check unconfirmed
//////////////////////////////////////////////////////////////////////////

public function check_unconfirmed() { 

	// Initialize
	global $config;

	// Go through inputs
	$rows = DB::query("SELECT * FROM coin_inputs WHERE is_confirmed = 0 AND confirmations >= %d", $config['btc_minconf']);
	foreach ($rows as $row) { 
		DB::query("UPDATE coin_inputs SET is_confirmed = 1 WHERE id = %d", $row['id']);
		execute_hooks('confirmed_deposit', $row['id']);
	}

}

//////////////////////////////////////////////////////////////////////////
// Send transaction
//////////////////////////////////////////////////////////////////////////

public function send_transaction($hexcode) { 

	try {
		$this->client->sendrawtransaction($hexcode);
	} catch (Exception $e) { 
		return false;
	}
	return true;
}

}

?>