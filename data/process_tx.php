<?php

// Load
$site_path = preg_replace("/data$/", "", realpath(dirname(__FILE__)));
require("$site_path/load.php");

// Check transaction
$client = new transaction();
$client->check_transaction($argv[1]);

// Exit
exit(0);

?>
