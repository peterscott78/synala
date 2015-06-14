
<h1>Alerts</h1>

{form}

<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Deposits</a></li>
		<li><a href="#tab2" data-toggle="tab">Users</a></li>
		<li><a href="#tab3" data-toggle="tab">Product Orders</a></li>
		<li><a href="#tab4" data-toggle="tab">Invoices Paid</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Deposits</h3><br /><br />

				<p>Below lists all new deposits that have been received since you last logged in.  Please note, this only shows deposits that are not assigned to a product purchase or invoice payment.</p>
			</div>

			{table alias="alerts" type="new_deposit"}<br />

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Alerts">Clear Checked Alerts</button> 
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All Deposit Alerts">Clear All Deposit Alerts</button>
			</center>
		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Users</h3><br /><br />

				<p>Below lists all new users that have registered since you previously logged in.</p>
			</div>

			{table alias="alerts" type="new_user"}<br />

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Alerts">Clear Checked Alerts</button> 
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All User Alerts">Clear All User Alerts</button>
			</center>
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Product Purchases</h3><br /><br />

				<p>Below lists all new product purchases since you previously logged in.</p>
			</div>

			{table alias="alerts" type="product_purchase"}<br />

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Alerts">Clear Checked Alerts</button> 
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All Product Alerts">Clear All Product Alerts</button>
			</center>
		</div>

		<div class="tab-pane" id="tab4">
			<div class="box-header with-border">
				<h3 class="box-title">Invoices Paid</h3><br /><br />

				<p>Below lists all invoices paid since you previously logged in.</p>
			</div>

			{table alias="alerts" type="invoice_paid"}<br />

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Alerts">Clear Checked Alerts</button> 
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All Invoice Alerts">Clear All Invoice Alerts</button>
			</center>			
		</div>
	</div>
</div><br>

<center><a href="{$site_uri}/admin/alerts?clearall=1" class="btn btn-info btn-sm">Clear All Alerts</a></center><br>


