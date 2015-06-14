
<script type="text/javascript">

	wallets = new Array();
	{$wallet_javascript}

	function addAddress() { 

		var table = document.getElementById('row_sendto_address');
		var num = table.rows.length + 1;
		var row = table.insertRow(num - 1);

		var cell0 = row.insertCell(0);
		cell0.innerHTML = 'Send To:';

		var cell1 = row.insertCell(1);
		cell1.innerHTML = 'Address: <input type="text" name="address' + num + '" style="width: 250px;">&nbsp;&nbsp;&nbsp; Amount: <input type="text" name="amount' + num + '" style="width: 70px;">';

	}

	function changeSigningMethod() { 
		document.getElementById('row_sendto_private_key').style.display = document.forms[0].signing_method[0].checked === true ? '' : 'none';
		changeWallet();
	}

	var required_sigs = {$required_sigs}
	function changeWallet() { 
		var box = document.forms[0].wallet_id;
		var wallet_id = box.type == 'hidden' ? box.value : box.options[box.selectedIndex].value;
		var num = wallets[wallet_id];
		
		var x = 0;
		var tbody = document.getElementById('row_sendto_private_key');
		if (num > required_sigs) { 
			temp_num = (required_sigs + 1);
			for (var x = (required_sigs + 1); x <= num; x++) { 
				var row = tbody.insertRow(tbody.rows.length);

				var cell0 = row.insertCell(0);
				cell0.innerHTML = 'BIP32 Private Key ' + String(x) + ':';

				var cell1 = row.insertCell(1);
				cell1.innerHTML = '<textarea name="private_key' + x + '"></textarea>';
			}

		} else if (required_sigs > num) { 
			for (x = required_sigs; x > num; x--) { 
				tbody.deleteRow((tbody.rows.length - 1));
			}
		}
		
		required_sigs = parseInt(num);
	}

</script>

<h1>Send Funds</h1>

{form enctype="multipart/form-data"}

<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Send Funds</a></li>
		<li><a href="#tab2" data-toggle="tab">Pending Sends</a></li>
		<li><a href="#tab3" data-toggle="tab">Upload Signed Sends</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Send Funds</h3><br /><br />

				<p>You may send funds by completing the below form.  You may choose to sign the transaction either online or offline.  If online, you will be prompted to enter the BIP32 private key(s).  Otherwise if offline, the send will be made available within the <i>Pending Sends</i> tab for signing.</p>
			</div>

			<table class="form_table">

			{if {$has_multiple_wallets} eq true}
			<tr>
				<td>From Wallet:</td>
				<td><select name="wallet_id" onchange="changeWallet();">{$wallet_options}</select></td>
			</tr>
			{else}
			<tr>
				<td>Balance Available:</td>
				<td>
					{$balance} BTC
					<input type="hidden" name="wallet_id" value="{$wallet_id}">
				</td>
			</tr>
			{/if}

			<tbody id="row_sendto_address">
			<tr>
				<td>Send To:</td>
				<td>
					Address: <input type="text" name="address1" style="width: 250px;">&nbsp;&nbsp;&nbsp;
					Amount: <input type="text" name="amount1" style="width: 70px;"> &nbsp;&nbsp;&nbsp;
					<a href="javascript:addAddress();"><img src="{$theme_uri}/icons/add.png" border="0" /> Add Address</a>
				</td>
			</tr>
			</tbody>

			<tr>
				<td>Optional Note:</td>
				<td><input type="text" name="note"></td>
			</tr><tr>
				<td>Signing Method:</td>
				<td>
					<input type="radio" name="signing_method" value="online" class="signing_method"> Online 
					<input type="radio" name="signing_method" value="offline" class="signing_method" checked="checked"> Offline 
				</td>
			</tr>

			<tbody id="row_sendto_private_key" style="display: none;">
				{$bip32_key_fields}
			</tbody>

			</table><br>

			{submit value="Send Funds"}

		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Pending Sends</h3><br /><br />

				<p>The below table lists all sends that are currently pending, and awaiting to be signed.  You may download the JSON file of all pending sends below, which can then be imported into the offline signer to securely sign all sends.  Alternatively, you may sign an individual send by clicking the desired <i>Send Tx</i> button below.</p>
			</div>

			{if {$has_multiple_wallets} eq true}
			<table class="form_table"><tr>
				<td>Wallet:</td>
				<td><select name="pending_wallet_id">{$wallet_options}</select></td>
			</tr></table>

			{else}
				<input type="hidden" name="pending_wallet_id" value="{$wallet_id}">
			{/if}

			{table alias="coin_sends" status="pending"}
			
			<center>
				<button type="submit" name="submit" class="btn btn-info" value="Delete Checked Sends">Delete Checked Sends</button>
				<button type="submit" name="submit" class="btn btn-info" value="Download JSON File">Download JSON File</button> 
			</center>

		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Upload Signed Sends</h3><br /><br />

				<p>Once you have successfully signed the pending sends via the offline signer, you must upload the resulting <i>signedtx.json</i> file below.  This will complete the sends, and broadcast them to the blockchain.</p>
			</div>

			<table class="form_table"><tr>
				<td>Signed JSON File:</td>
				<td><input type="file" name="signed_json"></td>
			</tr></table><br>

			{submit value="Upload Signed Sends"}<br>
		</div>
	</div>
</div>


