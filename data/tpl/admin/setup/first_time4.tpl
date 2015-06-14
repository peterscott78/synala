
<h1>First Time Setup - Step 4 / 5</h1>

{form action="admin/index"}
<input type="hidden" name="_setup_step" value="4">

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

	function changeAutoGenKeys() {
		var value = document.forms[0].autogen_keys[0].checked === true ? 1 : 0;
		document.getElementById('row_bip32_keys').style.display = value == 0 ? '' : 'none';
	}

</script>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">BIP32 Wallet</h3><br /><br />

		<p>Enter the specifications of your wallet below.  This allows you to choose whether or not to use standard addresses or multisig, and if so, how many signatures are required.  Below also asks for your BIP32 public key(s), which you may generate the keys from any source you wish.</p>

		<p>You may also use our <a href="http//envrin.com/offline_signer" target="_blank">Offline Signer</a> to generate them, or have Synala automtically generate them for you.  If you choose to have the keys automatically generated, the private key(s) will be displayed on the next page.  If you are unsure of what BIP32 is, please consult the <a href="http://synala.com/support/" target="_blank">User Manual</a>.</p>

		<p><b>NOTE:</b>  Please ensure to save your BIP32 private key(s) securely, as they are not stored anywhere within this system.  The private keys are required to spend any funds from your wallet.</p>
	</div>

	<table class="form_table"><tr>
		<td>Wallet Name:</td>
		<td><input type="text" name="wallet_name" value="Main Wallet"></td>
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

</div>

{submit value="Complete First Time Setup"}
