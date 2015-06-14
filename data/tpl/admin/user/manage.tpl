
<h1>Manage User</h1>

{form}

<p>You may manage any user in the database by entering their username or e-mail address below.  Partial searches are fine as well.</p>

{if {$show_user_list} eq true}
	{table alias="users" is_search="1"}

{else}
	<div class="box">
		<table class="form_table"><tr>
			<td>Username / E-Mail:</td>
			<td><input type="text" name="username"></td>
		</tr></table><br>

		{submit value="Manage User"}<br>
	</div>
{/if}
