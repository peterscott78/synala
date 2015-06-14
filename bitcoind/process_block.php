<?php

// Load
require("../load.php");

error_reporting(E_ALL);

// Process block
$client = new transaction();
$client->process_block($argv[1]);

// Exit
exit(0);

?>
