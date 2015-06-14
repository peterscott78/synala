<?php

// Initialize
global $template;

// Create new user
if (isset($_POST['submit']) && $_POST['submit'] == tr('Create New User')) { 
	$user = new User();
	$userid = $user->create();

	if ($template->has_errors != 1) {
		$template->add_message("Successfully created new user, $_POST[username]");
	}
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

?>