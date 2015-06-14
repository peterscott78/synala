
<script type="text/javascript">

	wallets = new Array();
	{$wallet_javascript}

	wallet_totals = new Array();
	{$wallet_totals_javascript}

	var required_sigs = {$required_sigs};
	var total_sigs = {$total_sigs};

	function changeWallet() { 
		var box = document.forms[0].wallet_id;
		var wallet_id = box.type == 'hidden' ? box.value : box.options[box.selectedIndex].value;
		var num = wallets[wallet_id];
		var total = wallet_totals[wallet_id];

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

		var x = 0;
		var tbody_public = document.getElementById('row_sendto_public_key');
		if (num > required_sigs) { 
			temp_num = (total_sigs + 1);
			for (var x = (total_sigs + 1); x <= total; x++) { 
				var prow = tbody_public.insertRow(tbody_public.rows.length);
				var pcell0 = prow.insertCell(0);
				pcell0.innerHTML = 'BIP32 Public Key ' + String(x) + ':';

				var pcell1 = prow.insertCell(1);
				pcell1.innerHTML = '<textarea name="public_key' + x + '"></textarea>';
			}

		} else if (total_sigs > num) { 
			for (x = total_sigs; x > num; x--) { 
				tbody_public.deleteRow((tbody_public.rows.length - 1));
			}
		}
				
		required_sigs = parseInt(num);
		total_sigs = parseInt(total);
	}

</script>

<h1>Transfer Wallet</h1>

{form}

<p>For security and privacy reasons, it is recommended you transfer your wallet to new BIP32 key(s) every so often (eg. every 90 days).  You may do so by completing the below form.  The remaining balance of your old wallet will be transferred to an address on the new public BIP32 key(s) you input, and all future incoming payments will be sent to the new BIP32 key(s).</p>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Current Wallet</h3><br /><br />

		<p>Enter the BIP32 private key(s) of your current wallet below.  For this transaction, online signing is acceptable, as the funds will only reside on your BIP32 keys for a matter of seconds after submitting the form, while they're transferred to the new BIP32 public key(s).  After which these BIP32 keys will be obsolete and can be discarded.</p>

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

		<tbody id="row_sendto_private_key">
			{$bip32_key_fields}
		</tbody>

		</table><br>

	</div>
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">New BIP32 Public Keys</h3><br /><br />

		<p>Enter the new BIP32 public keys below.  All funds on your current wallet, and all future incoming payments will be sent to the new BIP32 keys you specify below.</p>

		<table class="form_table">

		<tbody id="row_sendto_public_key">
			{$bip32_public_key_fields}
		</tbody>

		</table><br>

		{submit value="Transfer Wallet"}

	</div>
</div>

