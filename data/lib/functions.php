<?php

/////////////////////////////////////////////////////////////////////
// Error
//    $errno - Error # (eg. E_WARNING, E_STRICT, etc.)
//    $errmsg - Error message
//    $errfile - File error occurred on (ie. __FILE__)
//    $errline - Line error occurred on (ie. __LINE__)
//
//    NOTE:  Always use trigger_error(ERR_TYPE, $errmsg) to trigger 
//    an error within the software.  For the $errno use either:
//         - E_USER_ERROR - Software will abort, and display error template
//         - E_USER_WARNING - Warning, software will not abort
//         - E_USER_NOTICE - Notice, software will not abort
/////////////////////////////////////////////////////////////////////

function error($errno, $errmsg, $errfile, $errline) { 
	if ($errno == 2 && preg_match("/500 Internal Server Error/", $errmsg)) { throw new Exception("Error returned from bitcoin core"); }

	// Get log file to write to
	if ($errno == E_WARNING || $errno == E_USER_WARNING) { $logfile = 'warning'; }
	elseif ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_DEPRECATED || $errno == E_USER_DEPRECATED) { $logfile = 'notice'; }
	elseif ($errno == E_STRICT) { $logfile = 'strict'; }
	else { $logfile = 'error'; }

	// Save to logfile
	$origin = ($errno == E_USER_WARNING || $errno == E_USER_NOTICE || $errno == E_USER_ERROR || $errno == E_USER_DEPRECATED) ? 'USER' : 'PHP';
	$logline = $origin . ' - [' . date('Y-m-d H:i:s') . '] #' . $errno . ' ' . $errmsg . ' in (' . $errfile . ':' . $errline . ')';
	file_put_contents(SITE_PATH . '/data/log/' . $logfile, "$logline\n", FILE_APPEND);

	// Return, if not displaying error template
	if ($logfile != 'error') { return; }

	// Start template
	$template = new template('500');
	$template->assign('errno', $errno);
	$template->assign('errmsg', $errmsg);
	$template->assign('errfile', $errfile);
	$template->assign('errline', $errline);

	// Get theme
	if (preg_match("/^admin/", $_GET['route'])) { $template->theme = 'admin'; }

	// Display template
	$template->parse();
	exit(0);

}

/////////////////////////////////////////////////////////////////////
// Translate string of text
/////////////////////////////////////////////////////////////////////

function tr($string) { 
	return $string;
}

/////////////////////////////////////////////////////////////////////
// Get username
//    $uid - ID# of user
/////////////////////////////////////////////////////////////////////

function get_user($uid) { 
	return DB::queryFirstField("SELECT username FROM users WHERE id = %d", $uid);
}

/////////////////////////////////////////////////////////////////////
// Get user id
//    $username - Username
/////////////////////////////////////////////////////////////////////

function get_userid($username) { 
	if (!$userid = DB::queryFirstField("SELECT id FROM users WHERE username = %s", $username)) { $userid = 0; }
	return $userid;
}

/////////////////////////////////////////////////////////////////////
// Format amount
//    $amount - Amount to format
//    $currency - Currency to format in (default = EUR)
/////////////////////////////////////////////////////////////////////

function fmoney($amount) { 

	// Initialize
	global $config;
	if ($config['currency'] == 'EUR') { $new_amount = "&euro;"; }
	elseif ($config['currency'] == 'CNY') { $new_amount = "&yen;"; }
	elseif ($config['currency'] == 'RUB') { $new_amount = "&#x20bd;"; }
	else { $new_amount = '$'; }

	// Return
	$new_amount .= sprintf("%.2f", $amount);
	return $new_amount;

}

/////////////////////////////////////////////////////////////////////
// Format coin amount
/////////////////////////////////////////////////////////////////////

function fmoney_coin($amount) { 
	$amount = preg_replace("/0+$/", "", sprintf("%.8f", $amount));
	$decimals = strlen(substr(strrchr($amount, "."), 1));
	$amount = $decimals > 4 ? $amount : number_format($amount, 4);
	return $amount;
}

/////////////////////////////////////////////////////////////////////
// Get checkbox values
//    $var - Name of form field
/////////////////////////////////////////////////////////////////////

function get_chk($var) { 

	// Get values
	if (isset($_POST[$var]) && is_array($_POST[$var])) { $values = $_POST[$var]; }
	elseif (isset($_POST[$var])) { $values = array($_POST[$var]); }
	else { $values = array(); }

	// Return
	return $values;

}

/////////////////////////////////////////////////////////////////////
// Generate random string
//    $length - Length of string to generate (default = 6)
/////////////////////////////////////////////////////////////////////

function generate_random_string($length = 6) { 

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	// Generate random salt
	$string = '';
	for ($x = 1; $x <= $length; $x++) {
		$num = sprintf("%0d", rand(1, strlen($characters) - 1));
		$string .= $characters[$num];
	}
	
	// Return
	return $string;

}

/////////////////////////////////////////////////////////////////////
// Format human readable time from timestamp
//    $input - Timestamp to format
/////////////////////////////////////////////////////////////////////

function ftime($input) {

	// Format the input
	if (preg_match("/^\d\d\d\d-\d\d-\d\d /", $input)) { 
		$input = DB::queryFirstField("SELECT UNIX_TIMESTAMP(%s)", $input);
	} else { $input = strtotime($input); }

	// Get time
	$time = time() - $input;
	$seconds = $time % 60;

	// Set tokens
    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
	);

	// Go through tokens
	$string = '';
	foreach ($tokens as $unit => $text) {
		if ($time < $unit) { continue; }

		$numberOfUnits = floor($time / $unit);
		$string = $numberOfUnits . ' ' . $text;
		if ($numberOfUnits > 1) { $string .= 's'; }
		return $string;
	}

	// Return
	return $string;

}

/////////////////////////////////////////////////////////////////////
// Format date
/////////////////////////////////////////////////////////////////////

function fdate($date, $add_time = false) {

	// Split date, if needed
	if (preg_match("/^(.+?)\s(.+)/", $date, $match)) { $date = $match[1]; }

	// Format the date
	$elements = explode("-", $date);
	$new_date = date('F j, Y', mktime(0, 0, 0, $elements[1], $elements[2], $elements[0]));

	// Add time, if needed
	if ($add_time === true && preg_match("/^(.+)\:.+/", $match[2], $time_match)) {
		$new_date .= ' at ' . $time_match[1];
	}

	// Return
	return $new_date;

}

/////////////////////////////////////////////////////////////////////
// Update config var
//    $key - Name of variable to update
//    $value - Value to update it to
/////////////////////////////////////////////////////////////////////

function update_config_var($key, $value) { 
	global $config;
	DB::query("UPDATE config SET value = %s WHERE name = %s", $value, $key);
	$config[$key] = $value;
}

/////////////////////////////////////////////////////////////////////
// Get uploaded image contents (without exif tags)
//     $var - Name of form field input
/////////////////////////////////////////////////////////////////////

function get_uploaded_image($var = 'product_image', $width = 0, $height = 0) { 

	// Check
	if (!isset($_FILES[$var])) { return false; }
	if (!isset($_FILES[$var]['tmp_name'])) { return false; }
	if (!is_uploaded_file($_FILES[$var]['tmp_name'])) { return false; }

	// Get tmp file
	$tmpfile = tempnam(sys_get_temp_dir(), 'pi');

	// Strip exif
	if ($_FILES[$var]['type'] == 'image/gif') { 
		$image = imagecreatefromgif($_FILES[$var]['tmp_name']);
		imagegif($image, $tmpfile);
	} elseif ($_FILES[$var]['type'] == 'image/png') { 
		$image = imagecreatefrompng($_FILES[$var]['tmp_name']);
		imagepng($image, $tmpfile, 100);
	} elseif ($_FILES[$var]['type'] == 'image/jpg') { 
		$image = imagecreatefromjpeg($_FILES[$var]['tmp_name']);
		imagejpeg($image, $tmpfile, 100);
	} else { 
		return file_get_contents($_FILES[$var]['tmp_name']);
	}

	// Resize, if needed
	if ($width > 0 && $height > 0) { 
		$contents = generate_thumbnail($tmpfile, $width, $height);
	} else { $contents = file_get_contents($tmpfile); }

	// Return
	@unlink($tmpfile);
	return $contents;

}

/////////////////////////////////////////////////////////////////////
// Date - Add interval
/////////////////////////////////////////////////////////////////////

function date_add_interval($interval, $date = '') {
	if ($date == '') { $date = date('Y-m-d'); }

	// Check for proper interval
	if (!preg_match("/^(\w)(\d+)/", $interval, $match)) {
		return false;
	}

	// Get interval
	if ($match[1] == 'I') { $sql_interval = 'minute'; }
	elseif ($match[1] == 'H') { $sql_interval = 'hour'; }
	elseif ($match[1] == 'D') { $sql_interval = 'day'; }
	elseif ($match[1] == 'W') { $sql_interval = 'week'; }
	elseif ($match[1] == 'M') { $sql_interval = 'month'; }
	elseif ($match[1] == 'Y') { $sql_interval = 'year'; }
	else { return false; }

	// Get date
	if ($match[1] == 'I' || $match[1] == 'H') { 
		$new_date = DB::queryFirstField("SELECT date_add('$date', interval $match[2] $sql_interval)");
	} else { 
		$new_date = DB::queryFirstField("SELECT date(date_add('$date', interval $match[2] $sql_interval))");
	}
	
	// Return
	return $new_date;

}

/////////////////////////////////////////////////////////////////////
// Make thumbnails
/////////////////////////////////////////////////////////////////////

function generate_thumbnail($filename, $thumb_width, $thumb_height, $thumb_filename = '') {

	// Initialize
	global $config;

	// Get existing image
	if (!@list($width, $height, $type, $attr) = getimagesize($filename)) {
		return false;
	}
	
	// Initialize image
	if ($type == IMAGETYPE_GIF) { 
		@$source = imagecreatefromgif($filename);
		$ext = 'gif';
	} elseif ($type == IMAGETYPE_JPEG) { 
		@$source = imagecreatefromjpeg($filename);
		$ext = 'jpg';
	} elseif ($type == IMAGETYPE_PNG) { 
		@$source = imagecreatefrompng($filename);
		$ext = 'png';
	} else { return false; }
	
	// Get ratios
	$ratio_x = sprintf("%.2f", ($width / $thumb_width));
	$ratio_y = sprintf("%.2f", ($height / $thumb_height));

	// Resize image, if needed
	if ($ratio_x != $ratio_y) { 
		if ($ratio_x > $ratio_y) { 
			$new_width = $width;
			$new_height = ($height - sprintf("%.2f", ($height * ($ratio_x - $ratio_y)) / 100));
		} elseif ($ratio_y > $ratio_x) { 
			$new_height = $height;
			$new_width = ($width - sprintf("%.2f", ($width * ($ratio_y - $ratio_x)) / 100));
		}
		
		// Resize
		imagecopy($source, $source, 0, 0, 0, 0, $new_width, $new_height);
		$width = $new_width;
		$height = $new_height;
	}
	
	// Create thumbnail
	$thumb_source = imagecreatetruecolor($thumb_width, $thumb_height);
	imagecopyresized($thumb_source, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

	// Get thumb filename
	if ($thumb_filename == '') {
		$thumb_filename = tempnam(sys_get_temp_dir(), 'dhl');
		$delete_thumb = true;
	} else { $delete_thumb = false; }

	// Save thumbnail
	if ($type == IMAGETYPE_GIF) {
		imagegif($thumb_source, $thumb_filename);
	} elseif ($type == IMAGETYPE_JPEG) {
		imagejpeg($thumb_source, $thumb_filename);
	} elseif ($type == IMAGETYPE_PNG) {
		imagepng($thumb_source, $thumb_filename);
	} else { return false; }
	
	// Return file
	$thumb_contents = file_get_contents($thumb_filename);
	if ($delete_thumb === true) { @unlink($thumb_filename); }
	
	// Free memory
	imagedestroy($source);
	imagedestroy($thumb_source);

	// Return
	return $thumb_contents;

}

/////////////////////////////////////////////////////////////////////
// Execute hooks
/////////////////////////////////////////////////////////////////////

function execute_hooks($action, $id, $product_id = 0) { 

	// Get filename
	if ($action == 'product_purchased') { 
		$filename = SITE_PATH . '/data/hooks/products/' . $product_id . '.php';
		$function_name = 'hook_product_' . $product_id;
	} else {
		$filename = SITE_PATH . '/data/hooks/' . $action . '.php';
		$function_name = 'hook_' . $action;
	}

	// Check filename
	if (!file_exists($filename)) { return; }
	require_once($filename);
	if (!function_exists($function_name)) { return; }

	// Get vars, as needed
	$vars = array();
	if ($action == 'confirmed_deposit' || $action == 'invoice_paid' || $action == 'new_deposit' || $action == 'product_purchased') { 

		// Get input
		if (!$row = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE id = %d", $id)) { 
			trigger_error("Input does not exist, ID# $id", E_USER_ERROR);
		}

		// Set vars
		$vars = array(
			'input_id' => $row['id'], 
			'userid' => $row['userid'], 
			'username' => get_user($row['userid']), 
			'wallet_id' => $row['wallet_id'], 
			'product_id' => $row['product_id'], 
			'order_id' => $row['order_id'], 
			'invoice_id' => $row['invoice_id'], 
			'is_confirmed' => $row['is_confirmed'], 
			'is_spent' => $row['is_spent'], 
			'is_change' => $row['is_change'], 
			'confirmations' => $row['confirmations'], 
			'blocknum' => $row['blocknum'], 
			'address' => $row['address'], 
			'txid' => $row['txid'], 
			'vout' => $row['vout'], 
			'amount' => $row['amount'], 
			'date_added' => $row['date_added']
		);

		// Product vars, if needed
		if ($row['product_id'] > 0) { 
			$prow = DB::queryFirstRow("SELECT * FROM products WHERE id = %d", $row['product_id']);
			$vars['product_name'] = $prow['display_name'];
			$vars['product_description'] = $prow['description'];
			$vars['product_amount'] = $prow['amount'];
			$vars['product_currency'] = $prow['currency'];			
		}

		// Invoice vars, if needed
		if ($row['invoice_id'] > 0) { 
			$irow = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $row['invoice_id']);
			$row['invoice_status'] = $irow['status'];
			$row['invoice_amount'] = $irow['amount'];
			$row['invoice_amount_btc'] = $irow['amount_btc'];
			$row['invoice_amount_paid'] = $irow['amount_paid'];
			$row['invoice_currency'] = $irow['currency'];
			$row['invoice_note'] = $irow['note'];
		}

	} elseif ($action == 'funds_sent') { 

		// Get send
		if (!$row = DB::queryFirstRow("SELECT * FROM coin_sends WHERE id = %d", $id)) { 
			trigger_error("Send does not exist, ID# $id", E_USER_ERROR);
		}

		// Set vars
		$vars = array(
			'send_id' => $row['id'], 
			'wallet_id' => $row['wallet_id'], 
			'status' => $row['status'], 
			'amount' => $row['amount'], 
			'txid' => $row['txid'], 
			'date_added' => $row['date_added'], 
			'outputs' => array()
		);

		// Go through outputs
		$outputs = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d ORDER BY id", $id);
		foreach ($outputs as $output) { 
			$ovars = array(
				'address' => $output['address'], 
				'amount' => $output['amount']
			);
			array_push($vars['outputs'], $ovars);
		}

	} elseif ($action == 'new_user') { 

		// Get user row
		if (!$row = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $id)) { 
			trigger_error("User does not exist, ID# $id", E_USER_ERROR);
		}

		// Set vars
		$vars = array(
			'userid' => $row['id'], 
			'group_id' => $row['group_id'], 
			'status' => $row['status'], 
			'username' => $row['username'], 
			'full_name' => $row['full_name'], 
			'email' => $row['email'], 
			'date_created' => $row['date_created']
		);
	}

	// Execute function
	$function_name($vars);

}

/////////////////////////////////////////////////////////////////////
// Send notifications
/////////////////////////////////////////////////////////////////////

function send_notifications($action, $id) { 

	// Initialize
	global $config;

	// Get variables
	$userid = 0;
	if ($action == 'new_deposit' || $action == 'product_purchase' || $action == 'invoice_paid') { 

		// Get input
		$row = DB::queryFirstRow("SELECT * FROM coin_inputs WHERE id = %d", $id);
		$wallet_name = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
		$userid = $row['userid'];

		// Set vars
		$vars = array(
			'userid' => $row['userid'], 
			'username' => get_user($row['userid']), 
			'wallet_id' => $row['wallet_id'], 
			'wallet_name' => $wallet_name, 
			'product_id' => $row['product_id'], 
			'order_id' => $row['order_id'], 
			'invoice_id' => $row['invoice_id'], 
			'address' => $row['address'], 
			'txid' => $row['txid'], 
			'vout' => $row['vout'], 
			'amount' => $row['amount'], 
			'date_added' => fdate($row['date_added'], true)
		);

		// Product name
		if ($row['product_id'] > 0) { 
			$vars['product_name'] = DB::queryFirstField("SELECT display_name FROM products WHERE id = %d", $row['product_id']);
		} else { $vars['product_name'] = 'N/A'; }

		// Invoice name
		if ($row['invoice_id'] > 0) { 
			$irow = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $row['invoice_id']);
			$vars['invoice_name'] = "ID# $row[invoice_id] (" . fmoney_coin($row['amount_btc']) . ' BTC)';
		} else { $vars['invoice_name'] = 'N/A'; }

	} elseif ($action == 'funds_sent') { 

		// Get send
		$row = DB::queryFirstRow("SELECT * FROM coin_sends WHERE id = %d", $id);
		$wallet_name = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
		$userid = $row['userid'];

		// Get recipients
		$recipients = '';
		$recip_rows = DB::query("SELECT * FROM coin_sends_addresses WHERE send_id = %d ORDER BY id", $id);
		foreach ($recip_rows as $recip_row) { 
			$recipients .= $recip_row['address'] . ' - ' . $recip_row['amount'] . " BTC\n";
		}

		// Set vars
		$vars = array(
			'send_id' => $row['id'], 
			'wallet_id' => $row['wallet_id'], 
			'wallet_name' => $wallet_name, 
			'status' => ucwords($row['status']), 
			'amount' => $row['amount'], 
			'txid' => $row['txid'], 
			'date_added' => fdate($row['date_added'], true), 
			'recipients' => $recipients
		);

	} elseif ($action == 'invoice_created') { 

		// Get invoice
		$row = DB::queryFirstRow("SELECT * FROM invoices WHERE id = %d", $id);
		$wallet_name = DB::queryFirstField("SELECT display_name FROM coin_wallets WHERE id = %d", $row['wallet_id']);
		$userid = $row['userid'];

		// Set vars
		$vars = array(
			'invoice_id' => $row['id'], 
			'wallet_id' => $row['wallet_id'], 
			'wallet_name' => $wallet_name, 
			'userid' => $row['userid'], 
			'username' => get_user($row['userid']), 
			'status' => ucwords($row['status']), 
			'currency' => $row['currency'], 
			'amount' => $row['amount'], 
			'amount_btc' => $row['amount_btc'], 
			'amount_paid' => $row['amount_paid'], 
			'address' => $row['payment_address'], 
			'date_added' => fdate($row['date_added'], true), 
			'date_paid' => fdate($row['date_paid'], true), 
			'note' => $row['note'], 
			'process_note' => $row['process_note'], 
			'pay_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/' . SITE_URI . '/pay?invoice_id=' . $row['id']
		);
	}

	// Global variables
	$vars['site_name'] = $config['site_name'];
	$vars['company_name'] = $config['company_name'];

	// Go through notifications
	$rows = DB::query("SELECT * FROM notifications WHERE action = %s AND is_enabled = 1 ORDER BY id", $action);
	foreach ($rows as $row) { 

		// Get recipients
		if ($row['recipient'] == 'admin') { 
			$recipients = DB::queryFirstColumn("SELECT id FROM users WHERE group_id = 1 AND status = 'active' ORDER BY id");
		} else { $recipients = array($userid); }

		// Format message
		$contents = base64_decode($row['contents']);
		foreach ($vars as $key => $value) { 
			$row['subject'] = str_ireplace("~$key~", $value, $row['subject']);
			$contents = str_ireplace("~$key~", $value, $contents);
		}

		// Send message
		foreach ($recipients as $recipient) { 
			$email = DB::queryFirstField("SELECT email FROM users WHERE id = %d", $recipient);
			mail($email, $row['subject'], $contents);
		}

	}

}

/////////////////////////////////////////////////////////////////////
// Add alert
/////////////////////////////////////////////////////////////////////

function add_alert($action, $reference_id, $amount = 0) { 

	// Go through admins
	$admins = DB::queryFirstColumn("SELECT id FROM users WHERE group_id = 1 AND status = 'active'");
	foreach ($admins as $userid) { 
		if (!$userid > 0) { continue; }
		DB::insert('alerts', array(
			'userid' => $userid, 
			'type' => $action, 
			'reference_id' => $reference_id, 
			'amount' => $amount)
		);
	}

}


///////////////////////////////////////////////////////////////////////////////////
// Get coin exchange rate
///////////////////////////////////////////////////////////////////////////////////

function get_coin_exchange_rate($currency) { 

	// Send cURL request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://coinmarketcap-nexuist.rhcloud.com/api/btc');
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

	// Get result
	$response = curl_exec($ch);
	curl_close($ch);

	// Decode JSON
	$json = json_decode($response, true);

	// Get exchange rate
	$currency = strtolower($currency);
	if (isset($json['price']) && isset($json['price'][$currency])) { 
		return $json['price'][$currency];
	} else { return 0; }

}

/////////////////////////////////////////////////////////////////////
// Check for updates
/////////////////////////////////////////////////////////////////////

function check_updates() { 

	// Get version
	if ($row = DB::queryFirstField("SELECT * FROM config WHERE name = 'version'")) { 
		$version = $config['value'];
	} else { $version = 0.0; }

	// Upgrade, v0.3
	if ($version < 0.3) { 
		DB::insert('config', array('name' => 'blocknum', 'value' => '0'));
		DB::insert('config', array('name' => 'version', 'value' => '0.3'));
	}
	


}

?>
