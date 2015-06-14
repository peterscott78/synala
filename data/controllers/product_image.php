<?php

class controller_product_image { 

public function __construct($parts = array()) { 

	// Get product ID
	$product_id = preg_replace("/\.(.+)$/", "", $parts[1]);
	if (!$prow = DB::queryFirstRow("SELECT * FROM products_images WHERE id = %d", $product_id)) { 
		if (!$prow = DB::queryFirstRow("SELECT * FROM products_images WHERE id = 0")) { 
			echo "Invalid image"; exit(0);
		}
	}

	// Display image
	header("Content-type: $prow[mime_type]");
	echo base64_decode($prow['contents']);
	exit(0);

}

}

?>