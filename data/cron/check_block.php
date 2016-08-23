<?php

// Load
require("../../load.php");
global $config;

// Check block
$client = new transaction();
$client->check_block();

?>