
<h1>Manage Notification</h1>

{form action="admin/setup/notifications"}
<input type="hidden" name="notification_id" value="{$notify['id']}">

<p>Make any desired changes to the notification below, and submit the form to save all changes.</p>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Notification Details</h3>
	</div>

	<table class="form_table"><tr>
		<td>Is Enabled?:</td>
		<td>
			<input type="radio" name="is_enabled" value="1" {$chk_enabled_1}> Yes 
			<input type="radio" name="is_enabled" value="0" {$chk_enabled_0}> No 
		</td>
	</tr><tr>
		<td>Recipient:</td>
		<td>{$notify['recipient']}</td>
	</tr><tr>
		<td>Action:</td>
		<td>{$notify['display_name']}</td>
	</tr><tr>
		<td>Content-Type:</td>
		<td>
			<input type="radio" name="content_type" value="text/plain" {$chk_type_plain}> Plain Text 
			<input type="radio" name="content_type" value="text/html" {$chk_type_html}> HTML 
		</td>
	</tr><tr>
		<td>Subject:</td>
		<td><input type="text" name="subject" value="{$notify['subject']}"></td>
	</tr><tr>
		<td>Contents:</td>
		<td><textarea name="contents" style="height: 300px;">{$notify['contents']}</textarea></td>
	</tr></table><br>

	{submit value="Update Notification"}<br />
</div>

