
<h1>Outstanding Items</h1>

{form}

<div class="nav-tabs-custom">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Product Orders</a></li>
		<li><a href="#tab2" data-toggle="tab">Pending Invoices</a></li>
		<li><a href="#tab3" data-toggle="tab">Overpayments</a></li>
		<li><a href="#tab4" data-toggle="tab">Unauthorized Sends</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Product Orders</h3><br /><br />

				<p>Below shows all product orders that have been paid for, but not yet processed.  You may process and manage the orders as desired below.</p>
			</div>

			{table alias="orders" status="pending"}

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

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Pending Invoices</h3><br /><br />

				<p>The below table lists all pending invoices.  If desired, you may change the status of any invoices below, or view full details by clicking on the desired <i>Manage</i> button.</p>
			</div>

			{table alias="invoices" status="pending"}<br>

			<table class="form_table"><tr>
				<td>Status:</td>
				<td>
					<input type="radio" name="invoice_status" value="paid" checked="checked"> Paid 
					<input type="radio" name="invoice_status" value="cancelled"> Cancelled
				</td>
			</tr><tr>
				<td>Optional Note:</td>
				<td><input type="text" name="invoice_note"></td>
			</tr></table><br>

			{submit value="Process Checked Invoices"}<br>
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Overpayments</h3><br /><br />

				<p>The below table lists all overpayments that have been tracked.  These are when the user pays more than the product / invoice required.</p>
			</div>

			{table alias="coin_overpayments"}

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Overpayments">Clear Checked Overpayments</button>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All Overpayments">Clear All Overpayments</button>
			</center>
		</div>

		<div class="tab-pane" id="tab4">
			<div class="box-header with-border">
				<h3 class="box-title">Unauthorized Sends</h3><br /><br />

				<p>The below table lists all unauthorized sends that have been detected within the blockchain.  These are funds that were received by this system, but sent from outside of the system, and were not sent via the <i>Financial-&gt;Send Funds</i> menu.</p>
			</div>

			{table alias="coin_unauthorized_sends"}

			<center>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear Checked Unauthorized Sends">Clear Checked Unauthorized Sends</button>
				<button type="submit" name="submit" class="btn btn-info btn-sm" value="Clear All Unauthorized Sends">Clear All Unauthorized Sends</button>
			</center>
		</div>

	</div>

</div>

