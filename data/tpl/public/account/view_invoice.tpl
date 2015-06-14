
<h1>Manage Invoice</h1>

{form action="admin/financial/invoices"}
<input type="hidden" name="invoice_id" value="{$invoice['id']}">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Invoice Details</h3><br /><br />

		<p>Below shows all details on the selected invoice.  Make any desired changes to the information below, and submit the form to save all changes.</p>

		<table class="form_table"><tr>
			<td>Invoice ID#:</td>
			<td>{$invoice['id']}</td>
		</tr>

		{if {$has_multiple_wallets} eq true}
		<tr>
			<td>Pay to Wallet:</td>
			<td>{$invoice['wallet_name']}</td>
		</tr>
		{/if}

		<tr>
			<td>Status:</td>
			<td>{$invoice['status']}</td>
		</tr><tr>
			<td>Amount:</td>
			<td>{$invoice['amount_btc']} BTC ({$invoice['amount']})</td>
		</tr><tr>
			<td>Payment Address:</td>
			<td><a href="{$site_uri}/account/address?address={$invoice['payment_address']}">{$invoice['payment_address']}</a></td>
		</tr><tr>
			<td>Date Added:</td>
			<td>{$invoice['date_added']}</td>
		</tr><tr>
			<td>Date Paid:</td>
			<td>{$invoice['date_paid']}</td>
		</tr><tr>
			<td>Additional Note:</td>
			<td>{$invoice['note']}</td>
		</tr></table><br>

		{submit value="Update Invoice Details"}

	</div>
</div>

