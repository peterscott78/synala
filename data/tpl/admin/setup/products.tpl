
<h1>Product Settings</h1>

{form enctype="multipart/form-data"}

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Existing Products</h3><br /><br />

		<p>The below table lists all existing products you're previously created.  You may manage or delete any product, or receive the purchase link from below.</p>
	</div>

	{table alias="products"}
	{submit value="Delete Checked Products"}<br>
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Add New Product</h3><br /><br />

		<p>You may add a new product by completing the below form.  Upon doing so, you will receive a payment link 
		where customers may purchase the product.</p>
	</div>

	<table class="form_table"><tr>
		<td>Amount:</td>
		<td>
			<input type="text" name="amount" style="width: 80px;"> 
			<input type="radio" name="currency" value="fiat" class="flat-red" checked="checked"> {$config['currency']} 
			<input type="radio" name="currency" value="btc" class="flat-red"> BTC
		</td>
	</tr><tr>
		<td>Product Name:</td>
		<td><input type="text" name="product_name"></td>
	</tr><tr>
		<td>Description:</td>
		<td><textarea name="description"></textarea></td>
	</tr><tr>
		<td>Image <i>(optional)</i>:</td>
		<td><input type="file" name="product_image"></td>
	</tr></table><br>

	{submit value="Add New Product"}<br>
</div>

