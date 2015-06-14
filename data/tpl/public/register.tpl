
<h1>Register</h1>

{if {$registration_successful} eq true}

	<div class="row-centered">
		<div class="panel">
			<h2>Registration Successful</h2>

			<p>Thank you!  You have been successfully registered with the username <b>{$username}</b>, and may 
			now login with your new account.</p>
		</div>
	</div>

{else}

	{form action="register"}
	<input type="hidden" name="is_payment" value="{$is_payment}">
	<input type="hidden" name="wallet_id" value="{$wallet_id}">
	<input type="hidden" name="product_id" value="{$product_id}">
	<input type="hidden" name="amount" value="{$amount}">
	<input type="hidden" name="currency" value="{$currency}">

	<div class="row-centered">
		<div class="panel">
			<h2>Register</h2>

			<table class="form_table">

			{if {$config['username_field']} eq 'username'}
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username"></td>
			</tr>
			{/if}

			{if {$config['enable_full_name']} eq 1}
			<tr>
				<td>Full Name:</td>
				<td><input type="text" name="full_name"></td>
			</tr>
			{/if}

			<tr>
				<td>E-Mail Address:</td>
				<td><input type="text" name="email"></td>
			</tr><tr>
				<td>Password:</td>
				<td><input type="password" name="password"></td>
			</tr><tr>
				<td>Confirm Password:</td>
				<td><input type="password" name="password2"></td>
			</tr><tr>
				<td colspan="2"><br></td>
			</tr>
			{$custom_fields}
			</table><br>

			{submit value="Register Now"}<br>
		</div>
	</div><br>
{/if}
