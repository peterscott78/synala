<?php

class product {

//////////////////////////////////////////////////////////////////////////
// Construct
//////////////////////////////////////////////////////////////////////////

public function __construct($product_id = 0) { 
	$this->product_id = $product_id;
}

//////////////////////////////////////////////////////////////////////////
// Add new product
//////////////////////////////////////////////////////////////////////////

public function add_product($amount, $currency, $name, $description) { 

	// Add to DB
	DB::insert('products', array(
		'amount' => $amount, 
		'currency' => $currency, 
		'display_name' => $name, 
		'description' => $description)
	);
	$this->product_id = DB::insertId();

	// Add image, if needed
	if (isset($_FILES['product_image']) && isset($_FILES['product_image']['tmp_name']) && is_uploaded_file($_FILES['product_image']['tmp_name'])) { 
		$contents = base64_encode(file_get_contents($_FILES['product_image']['tmp_name']));
		DB::insert('products_images', array(
			'id' => $this->product_id, 
			'mime_type' => $_FILES['product_image']['type'], 
			'filename' => $_FILES['product_image']['name'], 
			'contents' => $contents)
		);
		@unlinK($_FILES['product_image']['tmp_name']);
	}

	// Return
	return $this->product_id;

}

}

?>