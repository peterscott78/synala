
<h1>View Transaction</h1>

{if {$is_input} eq true}
	<div class="box">
		<div class="box-header with-border">
			<h3 class="box-title">Payment Details</h3>
		</div>

		<table class="form_table"><tr>
			<td>Username:</td>
			<td>{$payment['username']}</td>
		</tr><tr>
			<td>Date Received:</td>
			<td>{$payment['date_received']}</td>
		</tr><tr>
			<td>Amount Received:</td>
			<td>{$payment['amount']}</td>
		</tr>

		{if {$payment['is_order']} eq true}
		<tr>
			<td>Order Details:</td>
			<td>{$payment['order_details']}</td>
		</tr>
		{/if}

		{if {$payment['is_invoice']} eq true}
		<tr>
			<td>Invoice Details:</td>
			<td>{$payment['invoice_details']}</td>
		</tr>
		{/if}

		</table>
	</div>
{/if}


<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Transaction Details</h3>
	</div>

	<table class="form_table"><tr>
		<td>TxID:</td>
		<td>{$txid}</td>
	</tr><tr>
		<td>Confirmations:</td>
		<td>{$confirmations}</td>
	</tr><tr>
		<td>Block #:</td>
		<td>{$blocknum}</td>
	</tr><tr>
		<td>Amount Input:</td>
		<td>{$input_amount} BTC</td>
	</tr><tr>
		<td>Amount Output:</td>
		<td>{$output_amount} BTC</td>
	</tr><tr>
		<td>Fees:</td>
		<td>{$fees} BTC</td>
	</tr></table>

</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Inputs</h3>
	</div>

	<table class="form_table">

	{section name=item loop=$inputs}
		<tr>
			<td>TxID:</td>
			<td><a href="{$site_uri}/admin/financial/tx?txid={$inputs[item].txid}">{$inputs[item].txid}</a> (vout: {$inputs[item].vout})</td>
		</tr><tr>
			<td>Amount:</td>
			<td>{$inputs[item].amount} BTC</td>
		</tr><tr>
			<td>ScriptSig (hex):</td>
			<td>{$inputs[item].scriptsig}</td>
		</tr><tr>
			<td colspan="2"><hr /></td>
		</tr>

	{/section}

	</table>
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Outputs</h3>
	</div>
	
	<table class="form_table">

	{section name=item loop=$outputs}
		<tr>
			<td>Address:</td>
			<td>{$outputs[item].address}</td>
		</tr><tr>
			<td>Amount:</td>
			<td>{$outputs[item].amount} BTC</td>
		</tr><tr>
			<td>ScriptSig:</td>
			<td>{$outputs[item].scriptsig}</td>
		</tr><tr>
			<td colspan="2"><hr/ ></td>
		</tr>
	{/section}

	</table>
</div>
