<?php

// Initialize
global $template, $auth;

// Logout
$client = new auth();
$client->logout();
$GLOBALS['userid'] = 0;

// User message
$template->add_message("You have been succesfully logged out.");

?>