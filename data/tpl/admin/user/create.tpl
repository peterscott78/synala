
<h1>Create New User</h1>

{form}

<p>You may create a new user by completing the below form with their profile information.  Upon creation, the user will be able to login, submit new payments, view their payment history, and more.</p>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Profile</h3><br /><br />
	</div>
	
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

	{submit value="Create New User"}<br>
</div>

