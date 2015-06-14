
<script type="text/javascript">

	var count = {$config['payment_expire_seconds']};
	var pay_hash = '{$pay_hash}';
{if {$amount_display} eq 'text'}
	var counter = setInterval(timer, 1000);
	var payment_checker = setInterval(check_payment, 5000);
{/if}

	function timer() {
		count = count - 1;
		if (count <= 0 && document.getElementById('panel_payment_pending').style.display != 'none') { 
			clearInterval(counter);
			document.getElementById('panel_payment_pending').style.display = 'none';
			document.getElementById('panel_payment_expired').style.display = 'block';			
		}
		document.getElementById("timer").innerHTML=count + " secs";
	}

	function check_payment() {

		$.getJSON("{$site_uri}/ajax/check_payment?pay_hash=" + pay_hash, function (data) {
			if (data['pay_status'] == 'approved') { 
				clearInterval(counter);
				clearInterval(payment_checker);
				document.getElementById('panel_payment_pending').style.display = 'none';
				document.getElementById('panel_payment_approved').style.display = 'block';
			} else if (data['pay_status'] == 'expired') { 
				clearInterval(counter);
				clearInterval(payment_checker);
				document.getElementById('panel_payment_pending').style.display = 'none';
				document.getElementById('panel_payment_expired').style.display = 'block';
			}
		});

	}

</script>

<h1>Submit Payment</h1>

<form action="{$site_uri}/pay" method="POST">
<input type="hidden" name="pay_hash" value="{$pay_hash}">
<input type="hidden" name="wallet_id" value="{$wallet_id}">
<input type="hidden" name="product_id" value="{$product_id}">
<input type="hidden" name="amount_hidden" value="{$amount_raw}">
<input type="hidden" name="currency_hidden" value="{$currency}">


<div class="row-centered"><div class="panel" id="panel_payment_pending">

	{if {$is_login} eq true}
		<h2>Your Account</h2>

		<table class="form_table">

		{if $config['username_field'] eq 'username'}
		<tr>
			<td>Username:</td>
			<td>{$user['username']}</td>
		</tr>
		{/if}

		{if $config['enable_full_name'] eq 1}
		<tr>
			<td>Full Name:</td>
			<td>{$user['full_name']}</td>
		</tr>
		{/if}

		<tr>
			<td>E-Mail:</td>
			<td>{$user['email']}</td>
		</tr></table>

	{else}
		<h2>Login Now</h2>

		<table class="form_table"><tr>
			<td>Username:</td>
			<td><input type="text" name="username" style="width: 150px;"></td>
		</tr><tr>
			<td>Password:</td>
			<td><input type="password" name="password" style="width: 150px;"></td>
		</tr></table><br>

		<center>
			<button type="submit" name="submit" class="btn btn-info" value="Login Now">Login Now</button> - or - 
			<a href="{$site_uri}/register?{$register_vars}" class="btn btn-info">Register</a>
		</center><br>
	{/if}

	<h2>Payment Details</h2>

	<table class="form_table"><tr>
		<td>Recipient:</td>
		<td>{$config['site_name']}</td>
	</tr>

	{if {$product_id} > 0}
	<tr>
		<td>Item Name:</td>
		<td>{$product_name}</td>
	</tr>
	{/if}

	{if {$amount_display} eq 'form'}
	<tr>
		<td>Amount:</td>
		<td>
			<input type="text" name="amount" style="width: 80px;"> 
			<input type="radio" name="currency" value="fiat" checked="checked"> {$config['currency']} 
			<input type="radio" name="currency" value="btc"> BTC
		</td>
	</tr><tr>
		<td colspan="2"><center>
			{submit value="Enter Amount"}
		</center></td>
	</tr>
	
	{elseif {$amount_display} eq 'text' or {$amount_display} eq 'amount_only'}
	<tr>
		<td>Amount:</td>
		<td>{$amount} BTC ({$amount_fiat} {$config['currency']})</td>
	</tr>
	{/if}

	{if {$amount_display} eq 'text'}
	<tr>
		<td>Payment Address:</td>
		<td>{$payment_address}</td>
	</tr><tr>
		<td colspan="2"><center>
			<img src="http://chart.apis.google.com/chart?cht=qr&chs=200x200&chl=bitcoin%3A{$payment_address}%3Famount%3D{$amount}&chld=H|0" width="200px" height="200px" border="0" />
		</center></td>
	</tr><tr>
		<td>Time Left:</td>
		<td><span id="timer"></span></td>
	</tr>
	{/if}

	</table>
</div>
		
<div class="panel" id="panel_payment_approved" style="display: none;">
	<h2>Payment Recieved!</h2>

	<p>Thank you!  Your payment for <b>{$amount} BTC</b> has been successfully received, and processed accordingly.</p>
</div>

<div class="panel" id="panel_payment_expired" style="display: none;">
	<h2>Payment Expired</h2>

	<p>We're sorry, but your payment has expired.  If you wish to continue, please reload this page to recalculate the amount, and re-start the payment process.</p>
</div>

</div>

</form>
