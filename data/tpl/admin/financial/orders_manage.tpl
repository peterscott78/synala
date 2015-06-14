
<h1>Manage Order</h1>

{form}
<input type="hidden" name="order_id" value="~order_id~">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Order Details</h3>
	</div>

	<table class="form_table"><tr>
		<td>Order ID#:</td>
		<td>{$order['id']}</td>
	</tr><tr>
		<td>Username:</td>
		<td><a href="{$site_uri}/admin/user/manage2?username={$order['username']}">{$order['username']}</a></td>
	</tr><tr>
		<td>Product:</td>
		<td>{$order['product_name']}</td>
	</tr><tr>
		<td>Status:</td>
		<td><select name="status">{$status_options}</select></td>
	</tr><tr>
		<td>Amount:</td>
		<td>{$order['amount']}</td>
	</tr><tr>
		<td>Order Date:</td>
		<td>{$order['date_added']}</td>
	</tr><tr>
		<td>Optional Note:</td>
		<td><textarea name="note">{$order['note']}</textarea></td>
	</tr></table><br>

	{submit value="Update Order Details"}<br>

	<div class="box-header with-border">
		<h3 class="box-title">Payments</h3>
	</div>

	{table alias="coin_inputs" order_id="~order_id~"}<br>

</div>
