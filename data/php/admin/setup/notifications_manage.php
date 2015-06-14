<?php

// Initialize
global $template;

// Get row
if (!$row = DB::queryFirstRow("SELECT * FROM notifications WHERE id = %d", $_REQUEST['notification_id'])) {
	trigger_error("Notification does not exist, ID# $_REQUEST[notification_id]", E_USER_ERROR);
}

// Set variables
$row['recipient'] = ucwords($row['recipient']);
$row['action'] = ucwords(str_replace("_", " ", $row['action']));
$row['contents'] = base64_decode($row['contents']);

// Is enabled checks
if ($row['is_enabled'] == 1) { 
	$template->assign('chk_enabled_1', 'checked="checked"');
	$template->assign('chk_enabled_0', '');
} else { 
	$template->assign('chk_enabled_1', '');
	$template->assign('chk_enabled_0', 'checked="checked"');
}

// Content type checks
if ($row['content_type'] == 'text/html') { 
	$template->assign('chk_type_plain', '');
	$template->assign('chk_type_html', 'checked="checked"');
} else { 
	$template->assign('chk_type_plain', 'checked="checked"');
	$template->assign('chk_type_html', '');
}

// Template variables
$template->assign('notify', $row);

?>