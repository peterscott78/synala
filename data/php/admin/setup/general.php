<?php

// Initialize
global $template, $config;

// Update general settings
if (isset($_POST['submit']) && $_POST['submit'] == tr('Update General Settings')) { 

	// Perform checks
	if (!is_numeric($_POST['payment_expire_seconds'])) { $template->add_message('Payment expire seconds must be an integer.', 'error'); }
	if (!is_numeric($_POST['btc_minconf'])) { $template->add_message('Confirmations required must be an integer.', 'error'); }
	if (!is_numeric($_POST['btc_txfee'])) { $template->add_message('Invalid amount submitted for base txfee.', 'error'); }

	// Update config, if no errors
	if ($template->has_errors != 1) { 

		// Set vars
		$vars = array(
			'site_name', 
			'company_name', 
			'username_field', 
			'enable_full_name', 
			'currency', 
			'payment_expire_seconds', 
			'btc_minconf', 
			'btc_txfee'
		);

		// Update config
		foreach ($vars as $var) { 
			update_config_var($var, $_POST[$var]);
		}

		// User message
		$template->add_message('Successfully updated general settings.');
	}


// Update security settings
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Update Security Settings')) { 

	// Perform checks
	if (!is_numeric($_POST['session_expire_mins'])) { $template->add_message('Session expire minutes must be an integer.', 'error'); }

	// Update, if no errors
	if ($template->has_errors != 1) { 

		// Set vars
		$vars = array(
			'session_expire_mins', 
			'enable_2fa', 
			'allowip'
		);

		// Update config
		foreach ($vars as $var) { 
			update_config_var($var, $_POST[$var]); 
		}

		// User message
		$template->add_message('Successfully updated security settings.');
	}

// Update bitcoin RPC settings
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Update Bitcoin RPC Settings')) { 

	// Set vars
	$vars = array(
		'btc_rpc_host', 
		'btc_rpc_user', 
		'btc_rpc_pass', 
		'btc_rpc_port'
	);

	// Update config
	foreach ($vars as $var) { 
		update_config_var($var, $_POST[$var]);
	}

	// User message
	$template->add_message('Successfully updated bitcoin RPC settings.');

// Update backup settings
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Update Backup Settings')) { 

	// Update
	update_config_var('backup_expire_days', $_POST['backup_expire_days']);
	update_config_var('backup_type', $_POST['backup_type']);
	update_config_var('backup_amazon_access_key', $_POST['backup_amazon_access_key']);
	update_config_var('backup_amazon_secret_key', $_POST['backup_amazon_secret_key']);
	update_config_var('backup_ftp_type', $_POST['backup_ftp_type']);
	update_config_var('backup_ftp_host', $_POST['backup_ftp_host']);
	update_config_var('backup_ftp_user', $_POST['backup_ftp_user']);
	update_config_var('backup_ftp_pass', $_POST['backup_ftp_pass']);
	update_config_var('backup_ftp_port', $_POST['backup_ftp_port']);
	update_config_var('backup_tarsnap_location', $_POST['backup_tarsnap_location']);
	update_config_var('backup_tarsnap_archive', $_POST['backup_tarsnap_archive']);

	// User message
	$template->add_message("Successfully updated backup settings");

// Backup now
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Download Backup Now')) { 

	// Backup
	$client = new backupmanager();
	$filename = $client->perform_backup(true);

	// Send headers
	header("Content-disposition: attachment; filename=\"$filename\"");
	header("Content-type: application/x-www-form-urlencoded");

	// Download file
	echo file_get_contents(SITH_PATH . '/data/backups/' . $filename);
	exit(0);

// Add profile field
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Add Profile Field')) { 

	// Add to db
	DB::insert('users_custom_fields', array(
		'form_field' => $_POST['profile_field_form_field'], 
		'display_name' => $_POST['profile_field_name'], 
		'options' => $_POST['profile_field_options'])
	);

	// User message
	$template->add_message("Successfully added new profile field, $_POST[profile_field_name]");

// Delete profile fields
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Delete Checked Fields')) { 

	// Delete
	$ids = get_chk('custom_field_id');
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("DELETE FROM users_custom_fields WHERE id = %d", $id);
	}

	// User message
	$template->add_message("Successfully deleted checked profile fields.");

}

// Define currencies
$currencies = array(
	'USD' => 'United States Dollar', 
	'EUR' => 'Euro', 
	'CNY' => 'Chinese Yuan', 
	'CAD' => 'Canadian Dollar', 
	'RUB' => 'Russian Ruble'
);

// Currency options
$currency_options = '';
foreach ($currencies as $abbr => $name) { 
	$chk = $abbr == $config['currency'] ? 'selected="selected"' : '';
	$currency_options .= "<option value=\"$abbr\" $chk>$name</option>";
}

// 2FA options
if ($config['enable_2fa'] == 'admin') { $twofa_options = '<option value="none">Disable</option><option value="admin" selected="selected">Admin Only</option><option value="all">All Users</option>'; }
elseif ($config['enable_2fa'] == 'all') { $twofa_options = '<option value="none">Disable</option><option value="admin">Admin Only</option><option value="all" selected="selected">All Users</option>'; }
else { $twofa_options = '<option value="none">Disable</option><option value="admin">Admin Only</option><option value="all">All Users</option>'; }

// Username field checkboxes
if ($config['username_field'] == 'email') { 
	$template->assign('chk_username_field_username', '');
	$template->assign('chk_username_field_email', 'checked="checked"');
} else { 
	$template->assign('chk_username_field_username', 'checked="checked"');
	$template->assign('chk_username_field_email', '');
}

// Enable full name checkboxes
if ($config['enable_full_name'] == 1) { 
	$template->assign('chk_enable_full_name_1', 'checked="checked"');
	$template->assign('chk_enable_full_name_0', '');
} else { 
	$template->assign('chk_enable_full_name_1', '');
	$template->assign('chk_enable_full_name_0', 'checked="checked"');
}

// Display variables
$display_backup_ftp = 'none';
$display_backup_amazon = 'none';
$display_backup_tarsnap = 'none';

// Backup options
if ($config['backup_type'] == 'ftp') {
	$backup_options = '<option value="local">Local Backups Only<option value="ftp" selected="selected">Remote FTP Server<option value="amazon">Amazon S3<option value="tarsnap">Tarsnap';
	$display_backup_ftp = '';
} elseif ($config['backup_type'] == 'amazon') { 
	$backup_options = '<option value="local">Local Backups Only<option value="ftp">Remote FTP Server<option value="amazon" selected="selected">Amazon S3<option value="tarsnap">Tarsnap';
	$display_backup_amazon = '';
} elseif ($config['backup_type'] == 'tarsnap') { 
	$backup_options = '<option value="local">Local Backups Only<option value="ftp">Remote FTP Server<option value="amazon">Amazon S3<option value="tarsnap" selected="selected">Tarsnap';
	$display_backup_tarsnap = '';
} else { $backup_options = '<option value="local" selected="selected">Local Backups Only<option value="ftp">Remote FTP Server<option value="amazon">Amazon S3<option value="tarsnap">Tarsnap'; }

// Backup FTP type checks
if ($config['backup_ftp_type'] == 'ftps') { 
	$template->assign('chk_backup_type_ftp', '');
	$template->assign('chk_backup_type_ftps', 'checked="checked"');
} else { 
	$template->assign('chk_backup_type_ftp', 'checked="checked"');
	$template->assign('chk_backup_type_ftps', '');
}

// Template variables
$template->assign('2fa_options', $twofa_options);
$template->assign('currency_options', $currency_options);
$template->assign('backup_options', $backup_options);
$template->assign('display_backup_ftp', $display_backup_ftp);
$template->assign('display_backup_amazon', $display_backup_amazon);
$template->assign('display_backup_tarsnap', $display_backup_tarsnap);

?>