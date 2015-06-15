
<h1>Manage User</h1>

{form action="admin/user/manage2"}
<input type="hidden" name="userid" value="{$user['id']}">

<div class="nav-tabs-custom">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Profile</a></li>
		<li><a href="#tab2" data-toggle="tab">Payments</a></li>
		<li><a href="#tab3" data-toggle="tab">Orders</a></li>
		<li><a href="#tab4" data-toggle="tab">Invoices</a></li>
		<li><a href="#tab5" data-toggle="tab">Addresses</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Profile</h3><br /><br />

				<p>Below lists the member's current profile.  You may make any desired changes below, and submit the form to save all changes.</p>
			</div>

			<table class="form_table"><tr>
				<td>Is Admin?:</td>
				<td>{boolean name="is_admin" checked="~is_admin~"}</td>
			</tr><tr>
				<td>Is Active?:</td>
				<td>{boolean name="is_active" checked="~is_active~"}</td>
			</tr>

			{if {$config['username_field']} eq 'username'}
			<tr>
				<td>Username:</td>
				<td><input type="text" name="new_username" value="{$user['username']}"></td>
			</tr>
			{/if}

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

			{submit value="Update User Profile"}<br>				

		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Payments</h3><br /><br />

				<p>Below lists all funds received from the user.  You may view full details on any transaction by clicking the desired <i>View Tx</i> button below.</p>
			</div>

			{table alias="coin_inputs" userid="~userid~"}<br>
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Orders</h3><br /><br />

				<p>Below lists all product orders made by this user.  You may view full details on any order by clicking the desired button below.</p>
			</div>

			{table alias="orders" userid="~userid~"}<br>

			<table class="form_table"><tr>
				<td>Status:</td>
				<td>
					<input type="radio" name="order_status" value="approved" checked="checked"> Approved 
					<input type="radio" name="order_status" value="declined"> Declined
				</td>
			</tr><tr>
				<td>Optional Note:</td>
				<td><textarea name="order_note"></textarea></td>
			</tr></table><br>

			{submit value="Process Checked Orders"}			
		</div>

		<div class="tab-pane" id="tab4">
			<div class="box-header with-border">
				<h3 class="box-title">Invoices</h3><br /><br />

				<p>Below lists all invoices created against this user's account.  You may view full details on any invoice by clicking the desired button below..</p>

				{table alias="invoices" userid="~userid~"}<br />
			</div>
		</div>

		<div class="tab-pane" id="tab5">
			<div class="box-header with-border">
				<h3 class="box-title">Addresses</h3><br /><br />

				<p>Below lists the all addresses assigned to the user's account.  You may view all transactions assigned to a specific address by clicking the desired link below.</p>

				{table alias="coin_addresses" userid="~userid~"}<br />
			</div>
		</div>

	</div>
</div>
