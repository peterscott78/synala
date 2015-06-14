
<h1>View Order</h1>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Order Details</h3>
	</div>

	<table class="form_table"><tr>
		<td>Order ID#:</td>
		<td>{$order['id']}</td>
	</tr><tr>
		<td>Product:</td>
		<td>{$order['product_name']}</td>
	</tr><tr>
		<td>Status:</td>
		<td>{$order['status']}</td>
	</tr><tr>
		<td>Amount:</td>
		<td>{$order['amount']}</td>
	</tr><tr>
		<td>Order Date:</td>
		<td>{$order['date_added']}</td>
	</tr></table><br>

	<div class="box-header with-border">
		<h3 class="box-title">Payments</h3>
	</div>

	{table alias="coin_inputs" order_id="~order_id~"}<br>

</div>
