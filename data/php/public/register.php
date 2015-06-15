<?php

// Initialize
global $template;
$registration_successful = false;

// Create new user
if (isset($_POST['submit']) && $_POST['submit'] == tr('Register Now')) { 
	$user = new User();
	$userid = $user->create();

	// Redirect to payment, if needed
	if ($template->has_errors != 1) { 

		// Login
		$auth = new auth();
		$auth->login('public', false);

		// Redirect, as needed
		if ($_POST['is_payment'] == 1) { 
			$template = new template('pay');
			$template->add_message("Successfully created new user, $_POST[username].  You may now login with your account.");
			$template->parse(); exit(0);

		} else {
			$template->add_message("Successfully created new user, $_POST[username].  You may now login with your account.");
		}
	}
}

// Set variables
if (isset($_REQUEST['is_payment']) && $_REQUEST['is_payment'] == 1) { 
	$is_payment = 1;
	$amount = $_REQUEST['amount'];
	$currency = $_REQUEST['currency'];
	$wallet_id = $_REQUEST['wallet_id'];
	$product_id = $_REQUEST['product_id'];
} else { 
	$is_payment = 0;
	$amount = 0;
	$currency = '';
	$wallet_id = 0;
	$product_id = 0;
}

// Go through custom fields
$custom_fields = '';
$rows = DB::query("SELECT * FROM users_custom_fields ORDER BY id");
foreach ($rows as $row) { 
	$custom_fields .= "<tr><td>" . $row['display_name'] . ":</td><td>";
	if ($row['form_field'] == 'text') { 
		$custom_fields .= "<input type=\"text\" name=\"custom" . $row['id'] . "\">";
	} elseif ($row['form_field'] == 'textarea') { 
		$custom_fields .= "<textarea name=\"custom" . $row['id'] . "\"></textarea>";
	} elseif ($row['form_field'] == 'boolean') { 
		$custom_fields .= "<input type=\"radio\" name=\"custom" . $row['id'] . "\" value=\"1\">Yes ";
		$custom_fields .= "<input type=\"radio\" name=\"custom" . $row['id'] . "\" value=\"0\" checked=\"checked\">No ";
	} elseif ($row['form_field'] == 'select') { 
		$options = explode("\n", $row['options']);
		$custom_fields .= "<select name=\"custom" . $row['id'] . "\">";
		foreach ($options as $option) { 
			$custom_fields .= "<option>$option</option>"; 
		}
		$custom_fields .= "</select>";
	}
	$custom_fields .= "</td></tr>";
}

// Assign variables
$template->assign('custom_fields', $custom_fields);
$template->assign('registration_successful', $registration_successful);
$template->assign('is_payment', $is_payment);
$template->assign('amount', $amount);
$template->assign('currency', $currency);
$template->assign('wallet_id', $wallet_id);
$template->assign('product_id', $product_id);

?>