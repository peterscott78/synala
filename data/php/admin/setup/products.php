<?php

// Initialize
global $template, $config;

// Add product
if (isset($_POST['submit']) && $_POST['submit'] == tr('Add New Product')) { 
	
	// Perform checks
	if ($_POST['amount'] == '') { $template->add_message("You did not specify a product amount.", 'error'); }
	elseif (!is_numeric($_POST['amount'])) { $template->add_message("Invalid product amount specified.", 'error'); }
	elseif ($_POST['amount'] < 0) { $template->add_message("Invalid product amount specified.", 'error'); }
	if ($_POST['product_name'] == '') { $template->add_message("You did not specify a product name", 'error'); }

	// Add product, if needed
	if ($template->has_errors != 1) { 
		$client = new product();
		$client->add_product($_POST['amount'], $_POST['currency'], $_POST['product_name'], $_POST['description']);
		$template->add_message("Successfully created new product, $_POST[product_name].");
	}

// Delete checked products
} elseif (isset($_POST['submit']) && $_POST['submit'] == tr('Delete Checked Products')) { 

	// Get IDs
	$ids = get_chk('product_id');

	// Disable
	foreach ($ids as $id) { 
		if (!$id > 0) { continue; }
		DB::query("UPDATE products SET is_enabled = 0 WHERE id = %d", $id);
	}

	// User message
	$template->add_message("Successfully deleted checked products.");

}


?>