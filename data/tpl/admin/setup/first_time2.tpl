
<h1>First Time Setup - Step 2 / 5</h1>

{form action="admin/index"}
<input type="hidden" name="_setup_step" value="2">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Administrator Details</h3><br /><br />

		<p>Enter the details for your administrator account below.  These are the login credentials you will use to access the administration panel in the future.</p>
	</div>

	<table class="form_table"><tr>
		<td>Username:</td>
		<td><input type="text" name="username"></td>
	</tr><tr>
		<td>E-Mail Address:</td>
		<td><input type="text" name="email"></td>
	</tr><tr>
		<td>Password:</td>
		<td><input type="password" name="password"></td>
	</tr><tr>
		<td>Confirm Password:</td>
		<td><input type="password" name="password2"></td>
	</tr></table><br>
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Site Details</h3><br /><br />

		<p>Below, enter basic details such as the site name and company name.  These will be displayed in the header and footer of your public site.</p>
	</div>

	<table class="form_table"><tr>
		<td>Site Name:</td>
		<td><input type="text" name="site_name" value="My Site"></td>
	</tr><tr>
		<td>Company Name:</td>
		<td><input type="text" name="company_name" value="My Company"></td>
	</tr></table><br>
</div>

{submit value="Continue to Next Step"}
