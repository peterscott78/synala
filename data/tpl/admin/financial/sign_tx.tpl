
<h1>Sign Transaction</h1>

{form action="admin/financial/send_funds"}
<input type="hidden" name="send_id" value="{$send_id}">
<input type="hidden" name="input_ids" value="{$input_ids}">

<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Sign Online</a></li>
		<li><a href="#tab2" data-toggle="tab">Sign Offline</a></li>
		<li><a href="#tab3" data-toggle="tab">Broadcast Tx</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Sign Online</h3><br /><br />

				<p>If desired, you may sign this transaction online by entering the appropriate BIP32 private keys below.  Upon submitting the form, the transaction will be automatically signed and broadcast to the blockchain.</p>
			</div>

			<table class="form_table">

			{section name=item loop=$sigs_required}
			<tr>
				<td>BIP32 Private Key #{$sigs_required[item].num}:</td>
				<td><textarea name="private_key{$sigs_required[item].num}" style="height: 60px;"></textarea></td>
			</tr>
			{/section}
			</table><br>

			{submit value="Sign Online Transaction"}
		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-title">
				<h3 class="box-title">Sign Offline</h3><br /><br />

				<p>You may sign this transaction offline by using the below information.  Copy and paste the below hex code into the offline signer, and enter the appropriate information.  Once done, enter the contents of the signed transaction into the <i>Broadcast Tx</i> tab on this page.</p>
			</div>

			<table class="form_table"><tr>
				<td>Hex Code:</td>
				<td><textarea name="hexcode" style="width: 600px; height: 110px;" readonly>{$hexcode}</textarea></td>
			</tr></table><br>

			<div class="box-header with-border">
				<h3 class="box-title">Inputs</h3>
			</div>

			<table class="form_table">

			{section name=item loop=$inputs}
				<tr>
					<td>TxID:</td>
					<td>{$inputs[item].txid} (vout: {$inputs[item].vout})</td>
				</tr><tr>
					<td>Amount:</td>
					<td>{$inputs[item].amount} BTC</td>
				</tr><tr>
					<td>ScriptSig (hex):</td>
					<td><textarea name="scriptsig" style="width: 600px; height: 70px;" readonly>{$inputs[item].scriptsig}</textarea></td>
				</tr><tr>
					<td>Key Index(es):</td>
					<td>{$inputs[item].keyindex}</td>
				</tr><tr>
					<td colspan="2"><hr /></td>
				</tr>
			{/section}

			</table>
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Broadcast Transaction</h3><br /><br />

				<p>Once you have successfully signed the transaction offline, you may enter the hex code of the signed transaction below to complete the send, and broadcast it to the blockchain.</p>
			</div>

			<table class="form_table"><tr>
				<td>Signed Hex Code:</td>
				<td><textarea name="signed_hex" style="width: 600px; height: 200px;"></textarea></td>
			</tr></table><br>

			{submit value="Broadcast Transaction"}<br>
		</div>
	</div>
</div>

