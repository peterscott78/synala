<?php

// Load
require("../load.php");

// Check transaction
$client = new transaction();
$client->check_transaction($argv[1]);

// Exit
exit(0);

?>
