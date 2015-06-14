<?php

// Load
require("../../load.php");
global $config;

// Backup
$client = new backupmanager();
$client->perform_backup(true);

// Exit
exit(0);

?>