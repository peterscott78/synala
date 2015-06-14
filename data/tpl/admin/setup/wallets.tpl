
<h1>Wallet Settings</h1>

{form}

<script type="text/javascript">

	var total_sigs = 1;

	function changeSigningMethod() {
		var type = document.forms[0].address_type[0].checked === true ? 'standard' : 'multisig';
		document.getElementById('row_multisig_format').style.display = type == 'multisig' ? '' : 'none';
		changeSigsRequried();
	}

	function changeSigsRequried() { 
		var num = document.forms[0].multisig_sig_total.value;
		if (!parseInt(num, 10)) { alert("You must enter an integer for the number of signatures required."); return false; }

		// Check address type
		if (document.forms[0].address_type[0].checked === true) { 
			num = 1;
		}

		var x = 0;
		var tbody = document.getElementById('row_bip32_keys');
		if (num > total_sigs) { 
			temp_num = (total_sigs + 1);
			for (var x = (total_sigs + 1); x <= num; x++) { 
				var row = tbody.insertRow(tbody.rows.length);

				var cell0 = row.insertCell(0);
				cell0.innerHTML = 'BIP32 Public Key ' + String(x) + ':';

				var cell1 = row.insertCell(1);
				cell1.innerHTML = '<textarea name="bip32_key' + x + '" style="width: 500px; height: 70px;"></textarea>';
			}

		} else if (total_sigs > num) { 
			for (x = total_sigs; x > num; x--) { 
				tbody.deleteRow((tbody.rows.length - 1));
			}
		}
		
		total_sigs = parseInt(num);
	}

	function generateMasterKey() { 

		$.getJSON("{$site_uri}/ajax/generate_master_bip32_key", function (data) {
			document.forms[0].gen_private_key.value = data['private_key'];
			document.forms[0].gen_public_key.value = data['public_key'];
		});

	}

	function changeAutoGenKeys() {
		var value = document.forms[0].autogen_keys[0].checked === true ? 1 : 0;
		document.getElementById('row_bip32_keys').style.display = value == 0 ? '' : 'none';
	}

	var required_sigs = 3
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

	wallets = new Array();
	{$wallet_javascript}

	var verify_required_sigs = {$required_sigs};
	function changeVerifyWallet() { 
		var box = document.forms[0].verify_wallet_id;
		var wallet_id = box.type == 'hidden' ? box.value : box.options[box.selectedIndex].value;
		var num = wallets[wallet_id];
		
		var x = 0;
		var tbody = document.getElementById('row_verify_public_key');
		if (num > verify_required_sigs) { 
			temp_num = (verify_required_sigs + 1);
			for (var x = (verify_required_sigs + 1); x <= num; x++) { 
				var row = tbody.insertRow(tbody.rows.length);

				var cell0 = row.insertCell(0);
				cell0.innerHTML = 'BIP32 Private Key ' + String(x) + ':';

				var cell1 = row.insertCell(1);
				cell1.innerHTML = '<textarea name="verify_private_key' + x + '"></textarea>';
			}

		} else if (verify_required_sigs > num) { 
			for (x = verify_required_sigs; x > num; x--) { 
				tbody.deleteRow((tbody.rows.length - 1));
			}
		}
		
		verify_required_sigs = parseInt(num);
	}

</script>

<div class="nav-tabs-custom">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">My Wallets</a></li>
		<li><a href="#tab2" data-toggle="tab">Generate Master Keys</a></li>
		<li><a href="#tab3" data-toggle="tab">Verify Public Key</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">My Wallets</h3><br /><br />

				<p>The below table lists all existing wallets you have created.  You may add a new wallet by completing the below form.</p>
			</div>

			{table alias="coin_wallets"}
			{submit value="Delete Checked Wallets"}<br>

			<div class="box-header with-border">
				<h3 class="box-title">Add New Wallet</h3><br /><br />

				<p>You may add a new wallet by completing the below form with the desired information.</p>
			</div>

			<table class="form_table"><tr>
				<td>Wallet Name:</td>
				<td><input type="text" name="wallet_name"></td>
			</tr><tr>
				<td>Address Type:</td>
				<td>
					<input type="radio" name="address_type" value="standard" class="signing_method" checked="checked"> Standard
					<input type="radio" name="address_type" value="multisig" class="signing_method"> Multisig
				</td>
			</tr><tr id="row_multisig_format" style="display: none;">
				<td>Signature Format:</td>
				<td>
					<input type="text" name="multisig_sig_required" value="2" style="width: 40px;"> of 
					<input type="text" name="multisig_sig_total" value="3" style="width: 40px;" onchange="changeSigsRequried();">
				</td>
			</tr><tr>
				<td>Auto-Generate BIP32 Keys?:</td>
				<td>
					<input type="radio" name="autogen_keys" class="autogen_keys" value="1"> Yes 
					<input type="radio" name="autogen_keys" class="autogen_keys" value="0" checked="checked"> No 
				</td>
			</tr>

			<tbody id="row_bip32_keys">
			<tr>
				<td>BIP32 Public Key:</td>
				<td><textarea name="bip32_key1" style="width: 500px; height: 70px;"></textarea></td>
			</tr>
			</tbody>

			</table><br>

			{submit value="Add New Wallet"}

		</div>

		<div class="tab-pane" id="tab2">		
			<div class="box-header with-border">
				<h3 class="box-title">Generate Master BIP32 Keys</h3><br /><br />

				<p>If desired you may generate new master BIP32 key pairs below.  All keys are generated using a random 8192 bit seed, helping ensure their security.</p>
			</div>

			<table class="form_table"><tr>
				<td>BIP32 Private Key:</td>
				<td><textarea name="gen_private_key" style="width: 500px; height: 70px;" readonly></textarea></td>
			</tr><tr>
				<td>BIP32 Public Key:</td>
				<td><textarea name="gen_public_key" style="width: 500px; height: 70px;" readonly></textarea></td>
			</tr></table><br>

			<center><a href="javascript:generateMasterKey();" class="btn btn-primary">Generate Master Key</a></center><br>
		</div>

		<div class="tab-pane" id="tab3">
			<div class="box-header with-border">
				<h3 class="box-title">Verify Public Key</h3><br /><br />

				<p>If desired, you may verify that the public key(s) stored within the system are correct, and have not been replaced by entering your private key(s) below.  The system will check to ensure the private keys you have entered match the public keys in the database.</p>
			</div>

			<table class="form_table">

			{if {$has_multiple_wallets} eq true}
			<tr>
				<td>Wallet:</td>
				<td><select name="verify_wallet_id" onchange="changeVerifyWallet();">{$wallet_options}</select></td>
			</tr>
			{/if}

			<tbody id="row_verify_public_key">
				{$bip32_key_fields}
			</tbody>

			</table><br>

			{submit value="Verify Public Key"}
		</div>

	</div>
</div><br>

