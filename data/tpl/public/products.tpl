
<h1>Products</h1>

<table class="form_table">{section name=item loop=$products}

<tr>
	<td><img src="{$site_uri}/product_image/{$products[item].id}.jpg" border="0" style="width: 100px; height: 100px;"></td>
	<td width="100%">
		<p><b>{$products[item].display_name}</b></p>
		<p>{$products[item].description}</p>
		<p><b>Price:</b> {$products[item].price} <a href="{$site_uri}/pay?product_id={$products[item].id}" class="btn btn-primary">Buy Now</a></p>
	</td>
</tr>

{/section}</table>
