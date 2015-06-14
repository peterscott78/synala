<?php

class encrypt {

//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct() { 


}

/////////////////////////////////////////////////////
// Get encryption algorithm
/////////////////////////////////////////////////////

private function get_algorithm() { 

	// Initialize
	global $config;

	// Check for mcrypt extension
	if (!extension_loaded('mcrypt')) { return 'text'; }
	if (!function_exists('mcrypt_encrypt')) { return 'text'; }
	if (!function_exists('mcrypt_decrypt')) { return 'text'; }
	if (!function_exists('mcrypt_get_iv_size')) { return 'text'; }
	if (!function_exists('mcrypt_create_iv')) { return 'text'; }

	// Return, if already assigned
	if ($config['mcrypt_algorithm'] != 'none') { return $config['mcrypt_algorithm']; }
	
	// Get supported algorithms
	$config['mcrypt_algorithm'] = 'text';
	$types = mcrypt_list_algorithms();
	$check_types = array('rijndael-256', 'rijndael-192', 'rijndael-128', 'cast-256', 'cast-128', 'blowfish', 'blowfish-compat', 'tripledes', 'des', 'twofish');

	// Check types
	foreach ($check_types as $type) { 
		if (in_array($type, $types)) { 
			$config['mcrypt_algorithm'] = $type;
			update_config_var('mcrypt_algorithm', $type);
			break;
		}
	}
	
	// Return
	return $config['mcrypt_algorithm'];
	
}


//////////////////////////////////////////////////////////////////////////
// Encrypt
//////////////////////////////////////////////////////////////////////////

public function encrypt($text) { 

	// Get alogrithm
	$algo = $this->get_algorithm();
	if ($algo == 'text') { return $text; }
	
	// Get IV
	$size = mcrypt_get_iv_size($algo, 'ecb');
	$iv = mcrypt_create_iv($size, MCRYPT_RAND);

	// Encrypt
	for ($x=1; $x < 7; $x++) { 
		$text = mcrypt_encrypt($algo, ENCRYPT_PASS, $text, 'ecb', $iv);
	}

	// Return
	return base64_encode($text);

}

//////////////////////////////////////////////////////////////////////////
// Decrypt
//////////////////////////////////////////////////////////////////////////

public function decrypt($text) { 

	// Get alogrithm
	$algo = $this->get_algorithm();
	if ($algo == 'text') { return $text; }
	
	// Get IV
	$size = mcrypt_get_iv_size($algo, 'ecb');
	$iv = mcrypt_create_iv($size, MCRYPT_RAND);

	// Encrypt
	$text = base64_decode($text);
	for ($x=1; $x < 7; $x++) { 
		$text = mcrypt_decrypt($algo, ENCRYPT_PASS, $text, 'ecb', $iv);
	}

	// Return
	return $text;

}

//////////////////////////////////////////////////////////////////////////
// Get password hash
//////////////////////////////////////////////////////////////////////////

public function get_password_hash($password, $userid) { 

	// Get user info
	if (!$user_row = DB::queryFirstRow("SELECT * FROM users WHERE id = %d", $userid)) { 
		return false;
	}

	// Generate salt
	$date_vars = explode(" ", preg_replace("/[-:]/", " ", $user_row['date_created']));
	$ip_vars = explode(" ", preg_replace("/[\.:]/", " ", $user_row['reg_ip']));
	$salt = array_sum($date_vars) + array_sum($ip_vars);

	// Ecncrypt
	$hash = $salt . $password . $salt;
	for ($x=1; $x < 32; $x++) { $hash = hash('sha512', $hash); }
	
	// Return
	return $hash;

}

}

?>