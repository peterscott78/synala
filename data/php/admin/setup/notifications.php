<?php

// Initialize
global $template;

// Change status
if (isset($_POST['submit']) && $_POST['submit'] == tr('Change Status of Checked Notifications')) { 

	// Change
	$ids = get_chk('notification_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("UPDATE notifications SET is_enabled = $_POST[is_enabled] WHERE id = %d", $id);
	}

	// User message
	$template->add_message('Successfully updated status of selected notifications.');

// Update notification
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Update Notification')) { 

	// Update db
	DB::update('notifications', array(
		'is_enabled' => $_POST['is_enabled'], 
		'content_type' => $_POST['content_type'], 
		'subject' => $_POST['subject'], 
		'contents' => base64_encode($_POST['contents'])), 
	"id = %d", $_POST['notification_id']);

	// User message
	$template->add_message('Successfully updated notification.');

}

?>