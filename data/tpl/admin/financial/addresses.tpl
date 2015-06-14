
<h1>Addresses</h1>

{form}

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Generate Address</h3>
	</div>

	{if {$has_multiple_wallets} eq true}
	<table class="form_table"><tr>
		<td>Wallet:</td>
		<td><select name="gen_wallet_id">{$wallet_options}</select></td>
	</tr>
	{/if}

	<tr>
		<td>Username:</td>
		<td><input type="text" name="gen_username" value="{$username}"></td>
	</tr></table><br>

	{submit value="Generate Address"}<br>
</div>	

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Existing Addresses</h3><br /><br />
	</div>

	<table class="form_table"><tr>

	{if {$has_multiple_wallets} eq true}
		<td align="left">
			Wallet: <select name="wallet_id" style="width: 200px;">{$wallet_options}</select> 
			<button type="submit" name="submit" class="btn btn-info btn-xs" value="View">View</button>
		</td>
	{/if}
		<td align="right">
			Search Address: <input type="text" name="search" style="width: 250px;">
			<button type="submit" name="submit" class="btn btn-info btn-xs" value="Search">Search</button>
		</td>
	</tr></table><br>

	{table alias="coin_addresses"}

</div>



