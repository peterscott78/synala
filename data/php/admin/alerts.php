<?php

// Initialize
global $template;

// Clear all
if (isset($_GET['clearall']) && $_GET['clearall'] == 1) { 
	DB::query("DELETE FROM alerts WHERE userid = %d", $GLOBALS['userid']);
	$template->add_message('Successfully cleared all alerts');

// Clear checked
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear Checked Alerts')) { 

	$ids = get_chk('alert_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("DELETE FROM alerts WHERE id = %d", $id);
	}
	$template->add_message('Successfully deleted all checked alerts.');

// Clear all deposit
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All Deposit Alerts')) { 
	DB::query("DELETE FROM alerts WHERE type = 'new_deposit' AND userid = %d", $GLOBALS['userid']);

// Clear all user
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All User Alerts')) { 
	DB::query("DELETE FROM alerts WHERE type = 'new_user' AND userid = %d", $GLOBALS['userid']);

// Clear all product
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All Product Alerts')) { 
	DB::query("DELETE FROM alerts WHERE type = 'product_purchase' AND userid = %d", $GLOBALS['userid']);

// Clear all invoice
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Clear All Invoice Alerts')) { 
	DB::query("DELETE FROM alerts WHERE type = 'invoice_paid' AND userid = %d", $GLOBALS['userid']);

}

?>