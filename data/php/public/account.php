<?php

// Initialize
global $template, $config;

// Update user profile
if (isset($_POST['submit']) && $_POST['submit'] == tr('Edit Profile')) { 

	// Update user
	$client = new user($GLOBALS['userid']);
	$client->update();

	// User message
	if ($template->has_errors != 1) { 
		$template->add_message("Successfully updated your profile");
	} else { 
		$template->theme = 'public';
		$template->route = 'public/account/edit_profile';
		$template->parse(); exit(0);
	}

}

?>