<?php

class rawtx {
	
//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct() { 

}

////////////////////////////////////////////////////////////////////////////
// Gather inputs
////////////////////////////////////////////////////////////////////////////

public function gather_inputs($wallet_id, $amount, $privkeys = array()) { 

	// Initialize
	global $config;
	$bip32 = new bip32();
	$enc = new encrypt();

	// Get wallet
	if (!$wallet = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $wallet_id)) { 
		trigger_error("Wallet does not exist, ID# $wallet_id", E_USER_ERROR);
	}

	// Go through inputs
	$inputs = array(); $input_amount = 0;
	$rows = DB::query("SELECT * FROM coin_inputs WHERE is_spent = 0 AND is_confirmed = 1 ORDER BY id");
	foreach ($rows as $row) { 
		if ($input_amount >= $amount) { break; }

		// Get address row
		if (!$addr_row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $row['address'])) { 
			continue;
		}

		// Multisig address
		if ($wallet['address_type'] == 'multisig') { 

			// Go through addresses
			$keys = array(); $public_keys = array();
			$arows = DB::query("SELECT * FROM coin_addresses_multisig WHERE address = %s ORDER BY id", $row['address']);
			foreach ($arows as $arow) { 

				// Get public key
				$keyindex = $addr_row['is_change_address'] . '/' . $arow['address_num'];
				$ext_pubkey = trim($enc->decrypt(DB::queryFirstField("SELECT public_key FROM coin_wallets_keys WHERE id = %d", $arow['key_id'])));
				$child_pubkey = $bip32->build_key($ext_pubkey, $keyindex)[0];
				$import = $bip32->import($child_pubkey);
				$public_keys[] = $import['key'];

				// Go through private keys
				foreach ($privkeys as $privkey) { 

					// Get child key
					$child_privkey = $bip32->build_key($privkey, $keyindex)[0];
					$chk_pubkey = $bip32->extended_private_to_public($child_privkey);
					if ($chk_pubkey != $child_pubkey) { continue; }

					// Validate privkey
					if (!in_array($child_privkey, $keys)) { $keys[] = $child_privkey; }
				}
			}
			if (count($keys) < $wallet['sigs_required']) { continue; }

			// Add to inputs
			$vars = array(
				'input_id' => $row['id'], 
				'txid' => $row['txid'], 
				'vout' => $row['vout'], 
				'amount' => $row['amount'], 
				'scriptsig' => $bip32->create_redeem_script($wallet['sigs_required'], $public_keys), 
				'public_keys' => $public_keys, 
				'privkeys' => $keys
			);
			array_push($inputs, $vars);
	
		// Standard address
		} else { 

			// Get private key
			$keyindex = $addr_row['is_change_address'] . '/' . $addr_row['address_num'];
			$privkey = $bip32->build_key($privkeys[0], $keyindex)[0];

			// Get script sig
			$decode_address = $bip32->base58_decode($row['address']);
			$scriptsig = '76a914' . substr($decode_address, 2, 40) . '88ac';

			// Get public key
			$public_key = DB::queryFirstField("SELECT public_key FROM coin_wallets_keys WHERE wallet_id = %d ORDER BY id LIMIT 0,1", $wallet_id);
			$public_key = trim($enc->decrypt($public_key));
			$child_pubkey = $bip32->build_key($public_key, $keyindex)[0];

			// Validate key
			$chk_pubkey = $bip32->extended_private_to_public($privkey);
			if ($chk_pubkey != $child_pubkey) { continue; }

			// Add to inputs
			$vars = array(
				'input_id' => $row['id'], 
				'txid' => $row['txid'], 
				'vout' => $row['vout'], 
				'amount' => $row['amount'], 
				'scriptsig' => $scriptsig, 
				'public_keys' => array($public_key), 
				'privkeys' => array($privkey)
			);
			array_push($inputs, $vars);
		}

		// Add to amounts
		$input_amount += $row['amount'];
		$amount += $config['btc_txfee'];
	}

	// Check amount
	if ($input_amount < $amount) { return false; }

	// Return
	return $inputs;

}

//////////////////////////////////////////////////////////////////////////
// Create transaction
//////////////////////////////////////////////////////////////////////////

public function create_transaction($wallet_id, $inputs, $outputs) { 

	// Initialize
	global $config;
	$p2sh_byte = TESTNET == 1 ? 'c4' : '05';
	$bip32 = new bip32();

	// Start variables
	$transaction = array(
		'01000000', 
		str_pad(count($inputs), 2, 0, STR_PAD_LEFT)
	);

	// Gather inputs
	$input_amount = floatval(0); $txfee = 0;
	foreach ($inputs as $row) {
		$transaction[] = bin2hex(strrev(hex2bin($row['txid']))) . bin2hex(pack('V', $row['vout']));
		$transaction[] = '00';
		$transaction[] = '';
		$transaction[] = 'ffffffff';
		$input_amount += (floatval($row['amount']) - floatval($config['btc_txfee']));
		$txfee += $config['btc_txfee'];
	}

	// Get amounts
	$output_amount = floatval(array_sum(array_values($outputs)));

	// Check for change
	if (round($input_amount, 8) > round($output_amount, 8)) { 
		$change_amount = (round($input_amount, 8) - round($output_amount, 8));
		$change_address = $bip32->generate_address($wallet_id, 0, 1);
		$outputs[$change_address] = round($change_amount, 8, PHP_ROUND_HALF_DOWN);
	}

	// Gather outputs
	$transaction[] = str_pad(count($outputs), 2, 0, STR_PAD_LEFT);
	foreach ($outputs as $address => $amount) { 

		// Get script sig
		$decode_address = $bip32->base58_decode($address);
		$version = substr($decode_address, 0, 2);
		$scriptsig = $version == $p2sh_byte ? 'a914' . substr($decode_address, 2, 40) . '87' : '76a914' . substr($decode_address, 2, 40) . '88ac';
		
		// Get amount
		$amount = $this->dec_to_bytes(($amount * 1e8), 8);
		$amount = $this->flip_byte_order($amount);

		// Add transaction vars
		$transaction[] = $amount;
		$transaction[] = dechex(strlen(hex2bin($scriptsig)));
		$transaction[] = $scriptsig;
	}

	// Return
	$transaction[] = '00000000';
	return $transaction;

}

//////////////////////////////////////////////////////////////////////////
// Sign transaction
//////////////////////////////////////////////////////////////////////////

public function sign_transaction($transaction, $inputs) { 

	// Initialize
	$bip32 = new bip32();

	// Start transaction
	$transaction[] = '01000000';
	$orig_transaction = $transaction;
	
	// Add inputs temporarily
	$temp_input = 3;
	foreach ($inputs as $input) { 
	
		// Set transaction variables
		$temp_transaction = $orig_transaction;
		if (count($input['privkeys']) > 1) { 
			$temp_transaction[$temp_input] = $this->encode_vint(strlen(hex2bin($input['scriptsig'])));
			$temp_transaction[($temp_input + 1)] = $input['scriptsig'];
		} else { 
			$temp_transaction[$temp_input] = dechex(strlen(hex2bin($input['scriptsig'])));
			$temp_transaction[($temp_input + 1)] = $input['scriptsig'];
		}

		// Initialize
		$generator = SECcurve::generator_secp256k1();
		
		// Hash structure
		$temp_hex_trans = pack("H*", implode("", $temp_transaction));
		$hash = hash('sha256', hash('sha256', $temp_hex_trans, true));
				
		// Go through the keys
		$signatures = array(); $total_sign=0;
		foreach ($input['privkeys'] as $privkey) { 

			// Get public key
			$import = $bip32->import($privkey);
			$pubkey = $bip32->private_to_public($import['key']);
			$cpubkey = $bip32->private_to_public($import['key'], true);

			// Get ready to sign
			$point = new Point($generator->getCurve(), gmp_init(substr($pubkey, 2, 64), 16), gmp_init(substr($pubkey, 66, 64), 16), $generator->getOrder());
			$_public_key = new PublicKey($generator, $point);
			$_private_key = new PrivateKey($_public_key, gmp_init($import['key'], 16));

			// Sign
			$sign = $_private_key->sign(gmp_init($hash, 16), gmp_init((string)bin2hex(openssl_random_pseudo_bytes(32)), 16));
			$signatures[$cpubkey] = $this->_encode_signature($sign);
			$total_sign++;
		}

		// Encode signature
		if (count($input['privkeys']) > 1) { 
			$sig = '00';
			foreach ($signatures as $pubkey => $sign) { 
				$sig .= $this->encode_vint(strlen($sign) / 2) . $sign;
			}
			$sig .= '4c' . $this->encode_vint(strlen($input['scriptsig']) / 2) . $input['scriptsig'];
		
		} else { 
			$key = array_keys($signatures)[0];
			$sig = $this->encode_vint(strlen($signatures[$key])/2) . $signatures[$key];
			$sig .= $this->encode_vint( strlen($key)/2) . $key;
		}
		
		// Add to transaction
		$transaction[$temp_input] = $this->encode_vint(strlen(hex2bin($sig)));
		$transaction[($temp_input + 1)] = $sig;
		$temp_input += 4;
	}
	array_pop($transaction);

	// Return
	return implode("", $transaction);
	
}

//////////////////////////////////////////////////////////////////////////
// Encode signature
//////////////////////////////////////////////////////////////////////////

public function _encode_signature(Signature $signature) { 

	// Init
	$client = new bip32();
	$s_max = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';
	$s_stabilizer = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';

	// Check for high S value
	$s = gmp_init($signature->getS(), 10);
	if (gmp_cmp($s, gmp_init($s_max, 16)) > 0) { 
		$s = gmp_sub(gmp_init($s_stabilizer, 16), $s);
	}

	// Pad r and s to 64 characters.
	$rh = str_pad($client->hex_encode($signature->getR()),64,'0', STR_PAD_LEFT);
	$sh = str_pad($client->hex_encode(gmp_strval($s)), 64, '0', STR_PAD_LEFT);
		
	// Check if the first byte of each has its highest bit set, 
	$t1 = unpack( "H*", (pack( 'H*',substr($rh, 0, 2)) & pack('H*', '80')));
	$t2 = unpack( "H*", (pack( 'H*',substr($sh, 0, 2)) & pack('H*', '80')));
	// if so, the result != 00, and must be padded.
	$r = ($t1[1] !== '00') ? '00'.$rh : $rh;
	$s = ($t2[1] !== '00') ? '00'.$sh : $sh;
		
	// Create the signature.
	$der_sig =  '30'
		. $this->dec_to_bytes( (4+((strlen($r)+strlen($s))/2)), 1) //((strlen($r)+strlen($s)+16)/2),1)
		.'02'
		. $this->dec_to_bytes(strlen($r)/2,1)
		. $r
		. '02'
		. $this->dec_to_bytes(strlen($s)/2,1)
		. $s
		. '01';

	// Return		
	return $der_sig;

}

//////////////////////////////////////////////////////////////////////////
// Validate signature
//////////////////////////////////////////////////////////////////////////

public function validate_signature($sig, $hash, $key) { 

	// Initialize
	$signature = $this->decode_signature($sig);
	$test_signature = new Signature(gmp_init($signature['r'],16), gmp_init($signature['s'],16));
	$generator = SECcurve::generator_secp256k1();
	$curve = $generator->getCurve();

	// Check key
	if(strlen($key) == '66') {
		$client = new BIP32();
		$decompress = $client->decompress_public_key($key);
		$public_key_point = $decompress['point'];
	} else {
		$x = gmp_strval(gmp_init(substr($key, 2, 64), 16), 10);
		$y = gmp_strval(gmp_init(substr($key, 66, 64), 16), 10);
		$public_key_point = new Point($curve, $x, $y, $generator->getOrder());
	}
	
	// Get hash
	$public_key = new PublicKey($generator, $public_key_point);
	$hash = gmp_init($hash, 16);

	// Return
	return $public_key->verifies($hash, $test_signature) === true;

}

//////////////////////////////////////////////////////////////////////////
// Decode signature
//////////////////////////////////////////////////////////////////////////

public function decode_signature($signature) { 

	// Get R
	$r_start = 8;
	$r_length = hexdec(substr($signature, 6, 2)) * 2;
	$r_end = $r_start + $r_length;
	$r = substr($signature, $r_start, $r_length);

	// Get S
	$s_start = $r_end + 4;
	$s_length = hexdec(substr($signature, ($r_end+2), 2))*2;
	$s = substr($signature, $s_start, $s_length);

	// Return	
	return array(
		'r' => $r, 
		's' => $s, 
		'hash_type' => substr($signature, -2), 
		'last_byte_s' => substr($s, -2)
	);
}

//////////////////////////////////////////////////////////////////////////
//
// UTILITY FUNCTIONS
//
//////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////
// Dec to bytes
//////////////////////////////////////////////////////////////////////////

public function dec_to_bytes($decimal, $bytes, $reverse = FALSE) {
	$hex = base_convert($decimal, 10, 16);
	if(strlen($hex) %2 != 0) { $hex = "0".$hex; }
	$hex = str_pad($hex, $bytes*2, "0", STR_PAD_LEFT);
	
	// Return
	if ($reverse === true) { $hex = bin2hex(strrev(hex2bin($hex))); }
	return $hex;
	
}

function flip_byte_order($bytes) { 
	return implode('', array_reverse(str_split($bytes, 2)));
}

//////////////////////////////////////////////////////////////////////////
// Get vint
//////////////////////////////////////////////////////////////////////////

public function get_varint($payload, $start = 0) { 

	// Check var int
	$x=1;
	$varint = strtolower(bin2hex(substr($payload, $start, 1)));
	if ($varint == 'fd') { 
		$num = unpack('v', substr($payload, ($start + 1), 2))[1];
		$x += 2;
	} elseif ($varint == 'fe') { 
		$num = unpack('v', substr($payload, ($start + 1), 4))[1];
		$x += 4;
	} elseif ($varint == 'ff') { 
		$num = unpack('V', substr($payload, ($start + 1), 8))[1];
		$x += 8;
	} else { 
		$num = unpack('C', hex2bin($varint))[1];
	}

	// Return
	return array($x, $num);

}

///////////////////////////////////////////////////////////////////////////
// Encode varint
///////////////////////////////////////////////////////////////////////////

public function encode_vint($decimal) {

	$hex = dechex($decimal);
	if ($decimal < 253) {
		$hint = $this->dec_to_bytes($decimal, 1);
		$num_bytes = 0;
	} else if($decimal < 65535) {
		$hint = 'fd';
		$num_bytes = 2;
	} else if($hex < 4294967295) {
		$hint = 'fe';
		$num_bytes = 4;
	} else if($hex < 18446744073709551615) {
		$hint = 'ff';
		$num_bytes = 8;
	} else {
		return FALSE;
	}

	// If the number needs no extra bytes, just return the 1-byte number.
	// If it needs to indicate a larger integer size (16bit, 32bit, 64bit)
	// then it returns the size hint and the 64bit number. 
	return ($num_bytes == 0) ? $hint : $hint . $this->dec_to_bytes($decimal, $num_bytes, true);

}

///////////////////////////////////////////////////////////////////////////
// Encode in to 64 bit
///////////////////////////////////////////////////////////////////////////

public function encode_64int($in, $pad_to_bits=64, $little_endian=true) {
	$in = decbin($in);
	$in = str_pad($in, $pad_to_bits, '0', STR_PAD_LEFT);
	$out = '';
	for ($i = 0, $len = strlen($in); $i < $len; $i += 8) {
		$out .= chr(bindec(substr($in,$i,8)));
	}
	if($little_endian) $out = strrev($out);
	return $out;
}


}

?>