<?php

// Load
require("../../load.php");
global $config;

// Get coin prices
$rate = get_coin_exchange_rate($config['currency']);
if ($rate != 0) { update_config_var('exchange_rate', $rate); }

// Exit
exit(0);

?>