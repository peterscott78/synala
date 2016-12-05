<?php

// Initialize
global $template;

// Check
$checks_ok = true;
if (!is_writeable(SITE_PATH . '/data/config.php')) { 
	$template->add_message("Unable to write to file at /data/config.php.  Please change file permissions appropriately, and reload the page.", 'error');
	$checks_ok = false;
}
if (!is_writeable(SITE_PATH . '/data/backups')) { 
	$template->add_message("Unable to write to directory at /data/backups/.  Please change directory permissions appropriately, and reload the page.", 'error');
	$checks_ok = false;
}
if (!is_writeable(SITE_PATH . '/data/log')) { 
	$template->add_message("Unable to write to directory at /data/log/.  Please change directory permissions appropriately, and reload the page.", 'error');
	$checks_ok = false;
}
if (!is_writeable(SITE_PATH . '/data/tpl_c')) { 
	$template->add_message("Unable to write to directory at /data/tpl_c/.  Please change directory permissions appropriately, and reload the page.", 'error');
	$checks_ok = false;
}

// Check PHP extensions
$extensions = array('openssl', 'curl', 'gmp', 'json', 'mcrypt', 'mysqli');
foreach ($extensions as $ext) { 
	if (!extension_loaded($ext)) {
		$template->add_message("The PHP extension <b>$ext</b> is not installed.  Please contact your server administrator, have this extension installed, and reload the page.", 'error');
		$checks_ok = false;
	}
}

// Template variables
$template->assign('checks_ok', $checks_ok);
$template->assign('dbname', (isset($_POST['dbname']) ? $_POST['dbname'] : ''));
$template->assign('dbuser', (isset($_POST['dbuser']) ? $_POST['dbuser'] : ''));
$template->assign('dbpass', (isset($_POST['dbpass']) ? $_POST['dbpass'] : ''));
$template->assign('dbhost', (isset($_POST['dbhost']) ? $_POST['dbhost'] : 'localhost'));
$template->assign('dbport', (isset($_POST['dbport']) ? $_POST['dbport'] : '3306'));
$template->assign('admin_user', (isset($_POST['username']) ? $_POST['username'] : ''));
$template->assign('admin_email', (isset($_POST['email']) ? $_POST['email'] : ''));
$template->assign('admin_pass', (isset($_POST['password']) ? $_POST['password'] : ''));

?>
