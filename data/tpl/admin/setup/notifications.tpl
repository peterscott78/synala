
<h1>Notifications</h1>

{form}

<p>From here you can manage all the various e-mail notifications that can be automatically sent out.</p>

<div class="box">
	{table alias="notifications"}<br>

	<table class="form_table"><tr>
		<td>Is Enabled:</td>
		<td>
			<input type="radio" name="is_enabled" value="1" checked="checked"> Yes 
			<input type="radio" name="is_enabled" value="0"> No
		</td>
	</tr></table><br>

	{submit value="Change Status of Checked Notifications"}<br>
</div>

