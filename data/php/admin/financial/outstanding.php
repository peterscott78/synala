<?php

// Initialize
global $template;

// Process orders
if (isset($_POST['submit']) && $_POST['submit'] == tr('Process Checked Orders')) { 

	// Process
	$ids = get_chk('order_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::update('orders', array(
			'status' => $_POST['order_status'], 
			'note' => $_POST['order_note']), 
		"id = %d", $id);
	}

	// User message
	$template->add_message('Successfully processed all checked orders');

// Clear checked overpayments
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear Checked Overpayments')) { 

	// Clear overpayments
	$ids = get_chk('overpayment_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("DELETE FROM coin_overpayments WHERE id = %d", $id);
	}
	$template->add_message('Successfully cleared all checked overpayments.');

// Clear all overpayments
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All Overpayments')) { 
	DB::query("DELETE FROM coin_overpayments");
	$template->add_message('Successfully cleared all overpayments.');

// Clear checked unauthorized sends
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear Checked Unauthorized Sends')) { 

	// Clear overpayments
	$ids = get_chk('unauthroized_send_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("DELETE FROM coin_unauthorized_sends WHERE id = %d", $id);
	}
	$template->add_message('Successfully cleared all checked unauthorized sends.');

// Clear all unauthorized sends
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All Unauthorized Sends')) { 
	DB::query("DELETE FROM coin_unauthorized_sends");
	$template->add_message('Successfully cleared all unauthorized sends.');
}


?>