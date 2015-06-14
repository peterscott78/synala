
<h1>First Time Setup - Step 1 / 5</h1>

{if {$checks_ok} eq true}

{form action="admin/index"}
<input type="hidden" name="_setup_step" value="1">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">mySQL Database</h3><br /><br />

		<p>Synala requires 1 clean mySQL database.  Enter your mySQL database information below.  If you do not know this information, please contact your web host / server administrator.</p>

		<table class="form_table"><tr>
			<td>DB Name:</td>
			<td><input type="text" name="dbname" value="{$dbname}"></td>
		</tr><tr>
			<td>DB User:</td>
			<td><input type="text" name="dbuser" value="{$dbuser}"></td>
		</tr><tr>
			<td>DB Pass:</td>
			<td><input type="text" name="dbpass" value="{$dbpass}"></td>
		</tr><tr>
			<td>DB Host:</td>
			<td><input type="text" name="dbhost" value="{$dbhost}"></td>
		</tr><tr>
			<td>DB Port:</td>
			<td><input type="text" name="dbport" value="{$dbport}" style="width: 100px;"></td>
		</tr></table><br>
	</div>
</div>

{submit value="Continue to Next Step"}

{/if}
