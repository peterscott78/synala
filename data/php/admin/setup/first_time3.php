<?php

// Initialize
global $template;

// Set RPC variables
if (isset($_POST['btc_rpc_host'])) { 
	$rpc_host = $_POST['btc_rpc_host'];
	$rpc_user = $_POST['btc_rpc_user'];
	$rpc_pass = $_POST['btc_rpc_pass'];
	$rpc_port = $_POST['btc_rpc_port'];
} else { 
	$rpc_host = '127.0.0.1';
	$rpc_user = generate_random_string(20);
	$rpc_pass = generate_random_string(20);
	$rpc_port = rand(5000, 9999);
}

// Template variables
$template->assign('rpc_host', $rpc_host);
$template->assign('rpc_user', $rpc_user);
$template->assign('rpc_pass', $rpc_pass);
$template->assign('rpc_port', $rpc_port);

?>