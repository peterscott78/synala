
<h1>Generate Invoice</h1>

{form}

{if {$has_multiple_wallets} eq false}
	<input type="hidden" name="wallet_id" value="{$wallet_id}">
{/if}

<div class="nav-tabs-custom">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Generate Invoice</a></li>
		<li><a href="#tab2" data-toggle="tab">Pending Invoices</a></li>
		<li><a href="#tab3" data-toggle="tab">Paid Invoices</a></li>
		<li><a href="#tab4" data-toggle="tab">Cancelled Invoices</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<h3>Generate Invoice</h3>

			<p>You may generate a new invoice by completing the below form.  An e-mail will be sent to the 
			user, notifying them of the pending invoice, and providing full instructions on how to pay.</p>

			<table class="form_table">

			{if {$has_multiple_wallets} eq true}
			<tr>
				<td>Pay to Wallet:</td>
				<td><select name="wallet_id">{$wallet_options}</select></td>
			</tr>
			{/if}

			<tr>
				<td>Username:</td>
				<td><input type="text" name="username"></td>
			</tr><tr>
				<td>Amount:</td>
				<td>
					<input type="text" name="amount" style="width: 80px;"> 
					<input type="radio" name="currency" value="fiat" checked="checked"> {$config['currency']} 
					<input type="radio" name="currency" value="btc"> BTC
				</td>
			</tr><tr>
				<td>Optional Note:</td>
				<td><textarea name="note"></textarea></td>
			</tr></table><br>

			{submit value="Generate Invoice"}
		</div>

		<div class="tab-pane" id="tab2">
			<h3>Pending Invoices</h3>

			<p>The below table lists all pending invoices.  If desired, you may change the status of any invoices below, or view full details by clicking on the desired <i>Manage</i> button.</p>

			{table alias="invoices" status="pending"}<br>

			<table class="form_table"><tr>
				<td>Status:</td>
				<td>
					<input type="radio" name="status" value="paid" checked="checked"> Paid 
					<input type="radio" name="status" value="cancelled"> Cancelled
				</td>
			</tr><tr>
				<td>Optional Note:</td>
				<td><input type="text" name="note"></td>
			</tr></table><br>

			{submit value="Process Checked Invoices"}<br>
		</div>


		<div class="tab-pane" id="tab3">
			<h3>Paid Invoices</h3>

			<p>The below table lists all previous invoices that have been marked as paid.  You may view full details on any invoice by clicking the desired <i>Manage</i> button.</p>

			{table alias="invoices" status="paid"}<br>
		</div>

		<div class="tab-pane" id="tab4">
			<h3>Cancelled Invoices</h3>

			<p>The below table lists all previous invoices that have been marked as cancelled.  You may view full details on any invoice by clicking the desired <i>Manage</i> button.</p>

			{table alias="invoices" status="cancelled"}<br>
		</div>
	</div>
</div>



