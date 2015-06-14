<?php

// Generate master key
$b32 = new bip32();
$private_key = $b32->generate_master_key();
$public_key = $b32->extended_private_to_public($private_key);

// Send response
$response = array(
	'private_key' => $private_key, 
	'public_key' => $public_key
);
echo json_encode($response);
exit(0);

?>