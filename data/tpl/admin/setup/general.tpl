
<script type="text/javascript">
	function changeProfileFormField(box) { 
		value = box.options[box.selectedIndex].value;
		document.getElementById('row_profile_field_options').style.display = value == 'select' ? '' : 'none';
	}

	function changeBackupType(box) { 
		var value = box.options[box.selectedIndex].value;
		document.getElementById('row_backup_type_amazon').style.display = value == 'amazon' ? '' : 'none';
		document.getElementById('row_backup_type_ftp').style.display = value == 'ftp' ? '' : 'none';
		document.getElementById('row_backup_type_tarsnap').style.display = value == 'tarsnap' ? '' : 'none';
	}
</script>

<h1>General Settings</h1>

{form}

<div class="nav-tabs-custom">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">General</a></li>
		<li><a href="#tab2" data-toggle="tab">Security</a></li>
		<li><a href="#tab3" data-toggle="tab">Bitcoind RPC</a></li>
		<li><a href="#tab4" data-toggle="tab">Backups</a></li>
		<li><a href="#tab5" data-toggle="tab">Profile Fields</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">General Settings</h3><br /><br />

				<p>Make any desires changes to the general settings below, and submit the form to save all changes.</p>
			</div>

			<table class="form_table"><tr>
				<td>Site Name: <i>(used in the site header):</i></td>
				<td><input type="text" name="site_name" value="{$config['site_name']}"></td>
			</tr><tr>
				<td>Company Name <i>(used in copyright footer):</i></td>
				<td><input type="text" name="company_name" value="{$config['company_name']}"></td>
			</tr><tr>
				<td>Username Field:</td>
				<td>
					<input type="radio" name="username_field" value="username" {$chk_username_field_username}> User Defined 
					<input type="radio" name="username_field" value="email" {$chk_username_field_email}> E-Mail Address
				</td>
			</tr><tr>
				<td>Request Full Name upon Registration?:</td>
				<td>
					<input type="radio" name="enable_full_name" value="1" {$chk_enable_full_name_1}> Yes 
					<input type="radio" name="enable_full_name" value="0" {$chk_enable_full_name_0}> No 
				</td>
			</tr><tr>
				<td>Currency:</td>
				<td><select name="currency">{$currency_options}</select></td>
			</tr><tr>
				<td>Seconds Payment Page Expires:</td>
				<td><input type="text" name="payment_expire_seconds" value="{$config['payment_expire_seconds']}" style="width: 80px;"> seconds</td>
			</tr><tr>
				<td>Confirmations Required:</td>
				<td><input type="text" name="btc_minconf" value="{$config['btc_minconf']}" style="width: 80px;"></td>
			</tr><tr>
				<td>Base Tx Fee:</td>
				<td><input type="text" name="btc_txfee" value="{$config['btc_txfee']}" style="width: 80px;"></td>
			</tr></table><br>

			{submit value="Update General Settings"}
		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Security Settings</h3><br /><br />

				<p>Below you can modify your security settings, such as 2FA, IP restrictions, and session length.</p>
			</div>

			<table class="form_table"><tr>
				<td><b>Session Length</b><br />Minutes of inactivity before you or any user is automatically logged out.</td>
				<td><input type="text" name="session_expire_mins" value="{$config['session_expire_mins']}" style="width: 60px;"> minutes</td>
			</tr><tr>
				<td><b>Enable 2FA</b><br />Whether or not to enable 2FA authentication for all users, just admins, or nobody.</td>
				<td><select name="enable_2fa">{$2fa_options}</select></td>
			</tr><tr>
				<td><b>IP Restrictions:</b><br />If desired, specify the IPs (one per-line) that are allowed access to the admin panel.</td>
				<td><textarea name="allowip" rows="7">{$config['ipallow']}</textarea></td>
			</tr></table><br>

			{submit value="Update Security Settings"}
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Bitcoin RPC Settings</h3><br /><br />

				<p>Below shows your current RPC settings for the bitcoind daemon, which you may modify as required.</p>
			</div>

			<table class="form_table"><tr>
				<td>RPC Host:</td>
				<td><input type="text" name="btc_rpc_host" value="{$config['btc_rpc_host']}"></td>
			</tr><tr>
				<td>RPC Username:</td>
				<td><input type="text" name="btc_rpc_user" value="{$config['btc_rpc_user']}"></td>
			</tr><tr>
				<td>RPC Password:</td>
				<td><input type="text" name="btc_rpc_pass" value="{$config['btc_rpc_pass']}"></td>
			</tr><tr>
				<td>RPC Port:</td>
				<td><input type="text" name="btc_rpc_port" value="{$config['btc_rpc_port']}" style="width: 80px;"></td>
			</tr></table><br>

			{submit value="Update Bitcoin RPC Settings"}
		</div>

		<div class="tab-pane" id="tab4">
			<div class="box-header with-border">
				<h3 class="box-title">Backups</h3><br /><br />

				<p>From below you may define your backup settings, and optionally where backups are automatically 
				uploaded, such as a remote FTP server, AWS account, or tarsnap.</p>
			</div>

			<table class="form_table"><tr>
				<td>Delete Backups After:</td>
				<td><input type="text" name="backup_expire_days" value="{$config['backup_expire_days']}" style="width: 70px;"> days</td>
			</tr><tr>
				<td>Backup Type:</td>
				<td><select name="backup_type" onchange="changeBackupType(this);">{$backup_options}</select></td>
			</tr>

			<tbody id="row_backup_type_amazon" style="display: {$display_backup_amazon};">
			<tr>
				<td>Amazon Access Key:</td>
				<td><input type="text" name="backup_amazon_access_key" value="{$config['backup_amazon_access_key']}"></td>
			</tr><tr>
				<td>Amazon Secret Key:</td>
				<td><input type="text" name="backup_amazon_secret_key" value="{$config['backup_amazon_secret_key']}"></td>
			</tr>
			</tbody>

			<tbody id="row_backup_type_ftp" style="display: {$display_backup_ftp};">
			<tr>
				<td>Connection:</td>
				<td>
					<input type="radio" name="backup_ftp_type" value="ftp" {$chk_backup_type_ftp}> FTP 
					<input type="radio" name="backup_ftp_type" value="ftps" {$chk_backup_type_ftps}> FTPS
				</td>
			</tr><tr>
				<td>FTP Host:</td>
				<td><input type="text" name="backup_ftp_host" value="{$config['backup_ftp_host']}"></td>
			</tr><tr>
				<td>FTP Username:</td>
				<td><input type="text" name="backup_ftp_user" value="{$config['backup_ftp_user']}"></td>
			</tr><tr>
				<td>FTP Password:</td>
				<td><input type="text" name="backup_ftp_pass" value="{$config['backup_ftp_pass']}"></td>
			</tr><tr>
				<td>FTP Port:</td>
				<td><input type="text" name="backup_ftp_port" value="{$config['backup_ftp_port']}" style="width: 80px;"></td>
			</tr>
			</tbody>

			<tbody id="row_backup_type_tarsnap" style="display: {$display_backup_tarsnap};">
			<tr>
				<td>Tarsnap Location:</td>
				<td><input type="text" name="backup_tarsnap_location" value="{$config['backup_tarsnap_location']}"></td>
			</tr><tr>
				<td>Tarsnap Archive Name:</td>
				<td><input type="text" name="backup_tarsnap_archive" value="{$config['backup_tarsnap_archive']}"></td>
			</tr>
			</tbody>

			</tr></table><br>

			{submit value="Update Backup Settings"}<br>

			<div class="box-header with-border">
				<h3 class="box-title">Download Backup Now</h3><br /><br />

				<p>If desired, you may instantly backup your entire system and download a copy of the backup by 
				pressing the below button.</p>
			</div>

			{submit value="Download Backup Now"}
		</div>

		<div class="tab-pane" id="tab5">
			<div class="box-header with-border">
				<h3 class="box-title">Profile Fields</h3><br /><br />

				<p>From below you may manage any custom profile fields you would like associated with each user's account.  Upon registration, these additional form fields will be displayed.</p>
			</div>

			{table alias="users_custom_fields"}
			{submit value="Delete Checked Fields"}<br>

			<div class="box-header with-border">
				<h3 class="box-title">Add New Profile Field</h3><br /><br />
			</div>

			<table class="form_table"><tr>
				<td>Field Name:</td>
				<td><input type="text" name="profile_field_name"></td>
			</tr><tr>
				<td>Form Field:</td>
				<td><select name="profile_field_form_field" onchange="changeProfileFormField(this);">
					<option value="text">Textbox</option>
					<option value="textarea">Large Textarea</option>
					<option value="boolean">Boolean (yes/no)</option>
					<option value="select">Select List</option>
				</select></td>
			</tr><tr id="row_profile_field_options" style="display: none;">
				<td>Options <i>(one per-line)</i>:</td>
				<td><textarea name="profile_field_options" style="height: 150px;"></textarea></td>
			</tr></table><br>

			{submit value="Add Profile Field"}

		</div>

	</div>

</div>



