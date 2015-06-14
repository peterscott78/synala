<?php

class controller_ajax { 

public function __construct($parts = array()) { 

	// Check if AJAX file exists
	$ajax_file = SITE_PATH . '/data/ajax/' . $parts[1] . '.php';
	if (!file_exists($ajax_file)) { 
		$response = array(
			'status' => 'error', 
			'message' => 'AJAX function does not exist, ' . $parts[1]
		);
		echo json_encode($response); exit(0);
	}

	// Load AJAX file
	require_once($ajax_file);

}

}

?>