<?php

// Initialize
global $template, $config;

// Update user profile
if (isset($_POST['submit']) && $_POST['submit'] == tr('Update User Profile')) { 

	// Update user
	$client = new user($_POST['userid']);
	$client->update();

	// User message
	if ($template->has_errors != 1) { 
		$template->add_message("Successfully updated user profile");
	}
	$_REQUEST['username'] = DB::queryFirstField("SELECT username FROM users WHERE id = %d", $_POST['userid']);

}

// Get user
if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE username = %s", $_REQUEST['username'])) { 
	trigger_error("Username does not exist, $_REQUEST[username]", E_USER_ERROR);
}
$_POST['userid'] = $user_row['id'];
$_POST['is_admin'] = $user_row['group_id'] == 1 ? 1 : 0;
$_POST['is_active'] = $user_row['status'] == 'active' ? 1 : 0;

// Go through custom fields
$custom_fields = '';
$custom_values = unserialize($user_row['custom_fields']);
$rows = DB::query("SELECT * FROM users_custom_fields ORDER BY id");
foreach ($rows as $row) { 
	$var = 'custom' . $row['id'];
	$value = isset($custom_values[$var]) ? $custom_values[$var] : '';

	$custom_fields .= "<tr><td>" . $row['display_name'] . ":</td><td>";
	if ($row['form_field'] == 'text') { 
		$custom_fields .= "<input type=\"text\" name=\"custom" . $row['id'] . "\" value=\"$value\">";
	} elseif ($row['form_field'] == 'textarea') { 
		$custom_fields .= "<textarea name=\"custom" . $row['id'] . "\">$value</textarea>";
	} elseif ($row['form_field'] == 'boolean') { 
		$custom_fields .= "<input type=\"radio\" name=\"custom" . $row['id'] . "\" value=\"1\">Yes ";
		$custom_fields .= "<input type=\"radio\" name=\"custom" . $row['id'] . "\" value=\"0\" checked=\"checked\">No ";
	} elseif ($row['form_field'] == 'select') { 
		$options = explode("\n", $row['options']);
		$custom_fields .= "<select name=\"custom" . $row['id'] . "\">";
		foreach ($options as $option) { 
			$chk = $value == $option ? 'selected="selected"' : '';
			$custom_fields .= "<option $chk>$option</option>"; 
		}
		$custom_fields .= "</select>";
	}
	$custom_fields .= "</td></tr>";
}

// Template variables
$template->assign('user', $user_row);
$template->assign('custom_fields', $custom_fields);

?>