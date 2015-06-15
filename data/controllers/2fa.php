<?php

class controller_2fa { 

public function __construct($parts = array()) { 

	// Check for row
	if (!$row = DB::queryFirstRow("SELECT * FROM auth_sessions WHERE 2fa_hash = %s AND 2fa_status = 0", $parts[1])) { 
		echo "Invalid 2FA request.  Please check the URL, and try again."; exit(0);
	}

	// Update
	DB::query("UPDATE auth_sessions SET 2fa_hash = '', 2fa_status = 1 WHERE id = %d", $row['id']);

	// Redirect, as needed
	$group_id = DB::queryFirstField("SELECT group_id FROM users WHERE id = %d", $row['userid']);
	if ($group_id == 1) { 
		header("Location: " . SITE_URI . "/admin/");
	} else {
		header("Location: " . SITE_URI);
	}

	// Exit
	exit(0);

}

}

?>