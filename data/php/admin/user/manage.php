<?php

// Initialize
global $template;
$show_user_list = false;

// Search users, if needed
if (isset($_POST['submit']) && $_POST['submit'] == tr('Manage User')) { 

	// Search for users
	$rows = DB::query("SELECT * FROM users WHERE username LIKE %ss OR email LIKE %ss", $_POST['username'], $_POST['username']);
	$num = DB::affectedRows();

	// Manage user
	if ($num > 1) { 
		$show_user_list = true;

	} elseif ($num == 1) { 
		$_REQUEST['username'] = $rows[0]['username'];
		$template = new template('admin/user/manage2');
		echo $template->parse(); exit(0);

	} else { $template->add_message("No users match the username or e-mail address $_POST[username].  Please try again."); }

}

// Template variables
$template->assign('show_user_list', $show_user_list);

?>