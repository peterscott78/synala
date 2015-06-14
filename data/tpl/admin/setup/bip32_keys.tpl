
<h1>BIP32 Keys</h1>

<p>Below shows your new BIP32 key pairs.  Please ensure to save the private key(s) listed below, as they are NOT stored within this software system.  You must have your private key(s) in order to send any funds.</p>

{section name=item loop=$keys}
	<div class="box">
		<div class="box-header with-border">
			<h3 class="box-title">BIP32 Key Pair #{$keys[item].num}</h3><br /><br />
		</div>

		<table class="form_table"><tr>
			<td>Private Key:</td>
			<td><textarea name="private_key" readonly>{$keys[item].private_key}</textarea></td>
		</tr><tr>
			<td>Public Key:</td>
			<td><textarea name="public_key" readonly>{$keys[item].public_key}</textarea></td>
		</tr></table><br />
	</div>
{/section}

