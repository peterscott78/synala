
<h1>Edit Profile</h1>

{form action="account"}

<div class="box">

	<table class="form_table">

	{if {$config['enable_full_name']} eq 1}
	<tr>
		<td>Full Name:</td>
		<td><input type="text" name="full_name" value="{$user['full_name']}"></td>
	</tr>
	{/if}

	<tr>
		<td>E-Mail Address:</td>
		<td><input type="text" name="email" value="{$user['email']}"></td>
	</tr><tr>
		<td>New Password <i>(optional)</i>:</td>
		<td><input type="password" name="password"></td>
	</tr><tr>
		<td>Confirm New Password:</td>
		<td><input type="password" name="password2"></td>
	</tr><tr>
		<td colspan="2"><br></td>
	</tr>
	{$custom_fields}
	</table><br>

	{submit value="Edit Profile"}<br>				

</div><br>

