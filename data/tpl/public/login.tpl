
<h1>Login</h1>

<form action="{$site_uri}/account" method="POST">

<div class="row-centered"><div class="panel">
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
		<a href="{$site_uri}/register" class="btn btn-info">Register</a>
	</center><br>
</div></div><br>
