<?php

// Load
$site_path = preg_replace("/bitcoind$/", "", realpath(dirname(__FILE__)));
require("$site_path/load.php");

error_reporting(E_ALL);

// Process block
$client = new transaction();
$client->process_block($argv[1]);

// Exit
exit(0);

?>
