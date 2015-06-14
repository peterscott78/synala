
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
			<td>Username:</td>
			<td><input type="text" name="username" value="{$invoice['username']}"></td>
		</tr><tr>
			<td>Status:</td>
			<td><select name="status">{$status_options}</select></td>
		</tr><tr>
			<td>Amount:</td>
			<td>
				<input type="text" name="amount" style="width: 80px;" value="{$invoice['primary_amount']}"> 
				<input type="radio" name="currency" value="fiat" {$chk_currency_fiat}> {$config['currency']} 
				<input type="radio" name="currency" value="btc" {$chk_currency_btc}> BTC
			</td>
		</tr><tr>
			<td>Amount ({$invoice['alt_currency']}):</td>
			<td>{$invoice['alt_amount']} {$invoice['alt_currency']}</td>
		</tr><tr>
			<td>Payment Address:</td>
			<td>{$invoice['payment_address']}</td>
		</tr><tr>
			<td>Date Added:</td>
			<td>{$invoice['date_added']}</td>
		</tr><tr>
			<td>Date Paid:</td>
			<td>{$invoice['date_paid']}</td>
		</tr><tr>
			<td>Optional Note:</td>
			<td><textarea name="note">{$invoice['note']}</textarea></td>
		</tr><tr>
			<td>Processing Note:</td>
			<td><textarea name="process_note">{$invoice['process_note']}</textarea></td>
		</tr></table><br>

		{submit value="Update Invoice Details"}

	</div>
</div>

