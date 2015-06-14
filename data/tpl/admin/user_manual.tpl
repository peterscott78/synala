
<h1>User Manual</h1>

<ol>
	<li>Settings</li>
</ol>


<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">BIP32 Wallets</h3>
	</div>

	<p>A BIP32 wallet allows one key pair (public and private key) to generate and control just over 2 billion payment addresses.  The main advantage of BIP32 is you can generate new addresses with just the public key itself, with no need to have the private key online at any time.  The private key is only required when you send funds, but not required to generate new payment addresses or accept payments.</p>

	<p>You can generate the BIP32 keys from any source you wish.  If desired, first time setup also allows you to auto-generate the BIP32 keys (you'll receive the private keys on the next page), or you may also use our <a href="http://envrin.com/offline_signer" target="_blank">Offline Signer</a> to generate them as well.  Each BIP32 key pair comprises of a public and private key, which will look something like:</p>

<pre>
xprv9vAGLpacek2812CqcB6AAgaaEHdjyxNwySpfTDMjY58qKjiXWyaZMSoVtjwFx8P9qVjyyRMnfDWRUXn9pfugzuLT1qmwNJc989G4tmv8y9g
xpub699ckL7WV7aRDWHJiCdAXpXJnKUEPR6oLfkGFbmM6QfpCY3g4WtouF7yk44MUN7rAXi6W3vVydGRx6Q2sD6ajT4XcJQ7qyQ6xZFUMNzE9iM
</pre>

	<p>You will be required to enter your BIP32 key(s) during first time setup, and may also manage additional wallets via the Settings-&gt;Wallets menu.  You can either choose standard addresses (begin with a '1', and require only one BIP32 key pair) or multisig (begin with a '3', and require multiple BIP32 key pairs).  For example, you may wish to use 2-of-3 multisig, requiring two private keys for all sends helping keep your funds more secure, plus you can lose one key without losing access to your funds.  If you are unsure, simply leave the address type at <i>Standard</i>.</p>

	<p><b>IMPORTANT:</b>  The BIP32 private keys are NOT stored anywhere within this system.  Make sure you save them securely somewhere, as you will need them to conduct any sends.  If you lose your BIP32 private keys, you will permanently lose all access to your funds!</p><br />
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Accepting Payments</h3>
	</div>

	<p>Once setup is complete, you may begin accepting payments by directing visitors to your payment page at:</p>

	<blockquote><a href="http://{$http_host}/pay">http://{$http_host}/pay</a></blockquote>

	<p>They are multiple variables you may add into the query string, which are described in the below table.</p>

	<table class="table table-bordered table-striped"><thead><tr>
		<th>Variable</th>
		<th>Description</th>
	</tr></thead>

	<tbody>
	<tr>
		<td>amount</td>
		<td>Specify the amount to pay.</td>
	</tr><tr>
		<td>currency</td>
		<td>Can be either "btc" or "fiat" (defaults to "fiat"), and let's you define which currency the amount is in.  Only required if you specify an "amount" variable.</td>
	</tr><tr>
		<td>wallet_id</td>
		<td>Only applicable if you're using multiple wallets, and allows you to specify the ID# of the wallet the funds will go to.</td>
	</tr><tr>
		<td>product_id</td>
		<td>Only applicable if you have added products via the Setup-&gt;Products tab, and is the ID# of the product being purchased.</td>
	</tr><tr>
		<td>invoice_id</td>
		<td>Only applicable if you've generated invoices against users, and is the ID# of the invoice being paid.</td>
	</tr>
	</tbody>
	</table><br>

	<p>For example, to charge someone $50.00 or 0.35 BTC you would use:</p>

	<blockquote>
		<a href="http://{$http_host}/pay?amount=50">http://{$http_host}/pay?amount=50</a><br />
		<a href="http://{$http_host}/pay?amount=0.35&currency=BTC">http://{$http_host}/pay?amount=0.35&amp;currency=BTC</a><br />
	</blockquote>

	<p>If a <i>product_id</i> or <i>invoice_id</i> is specified, then the amount of the product / invoice will be charged.  Otherwise, if no amount is specified, the user will be asked to specify the amount they would like to send.  The exchange rate is automatically updated every 30 minutes, and is retrieved from the <a href="http://coinmarketcap.com/" target="_blank">CoinMarketCap.Com</a> website.</p><br />
</div>

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Sending Funds</h3>
	</div>

	<p>There are multiple different ways you can send funds, all of which can be initiated through the Financial-&gt;Send Funds menu.</p>

</div>
