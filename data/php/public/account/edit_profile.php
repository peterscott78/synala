<?php

// Initialize
global $template, $config;

// Get user
if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $GLOBALS['userid'])) { 
	trigger_error("User ID does not exist, $GLOBALS[userid]", E_USER_ERROR);
}
$_POST['userid'] = $user_row['id'];

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