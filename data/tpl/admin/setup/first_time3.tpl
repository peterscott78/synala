
<h1>First Time Setup - Step 3 / 5</h1>

{form action="admin/index"}
<input type="hidden" name="_setup_step" value="3">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Bitcoind RPC</h3><br /><br />

		<p>Synala requires that you have bitcoind running on your server, in order to watch transactions flow through the blockchain.  If you do not already have bitcoind on this server, please download Bitcoin Core from the <a href="https://bitcoin.org/en/download" target="_blank">Bitcoin.Org Download Page</a>, and upload the /bin/bitcoind and /bin/bitcoin-cli files to your server.</p>

		<p>To use the bitcoind binary included with Synala, simply complete the following steps:</p>

		<ol>
			<li>Login to your server, and create the file and directory at $HOME/.bitcoin/bitcoin.conf, with the contents being the below sample bitcoin.conf file.</li>
			<li>SSH into your server, move to the directory where you uploaded <i>bitcoind</i>, and simply type: "./bitcoind"</li>
			<li>Submit the below form as is.</li>
		</ol>

		<p>If you'll be setting up bitcoind yourself, or already have it installed, below is a sample bitcoin.conf configuration file.  The username, password and port are randomly generated, but can be changed to anything you desire.  Please take note of the lines in red, as they are required for Synala to work correctly.</p>

		<p><b>Sample bitcoin.conf file</b></p>

<pre>
<font color="#cc0000">daemon=1</font>
<font color="#cc0000">txindex=1</font>
rpcuser=<i>{$rpc_user}</i>
rpcpassword=<i>{$rpc_pass}</i>
rpcport=<i>{$rpc_port}</i>
<font color="#cc0000">walletnotify=/usr/bin/php -q {$site_path}/data/process_tx.php %s</font>
</pre>

		<p>To continue with setup, please enter the RPC connection information for bitcoind below.</p>

		<table class="form_table"><tr>
			<td>RPC Host:</td>
			<td><input type="text" name="btc_rpc_host" value="{$rpc_host}"></td>
		</tr><tr>
			<td>RPC Username:</td>
			<td><input type="text" name="btc_rpc_user" value="{$rpc_user}"></td>
		</tr><tr>
			<td>RPC Password:</td>
			<td><input type="text" name="btc_rpc_pass" value="{$rpc_pass}"></td>
		</tr><tr>
			<td>RPC Port:</td>
			<td><input type="text" name="btc_rpc_port" value="{$rpc_port}" style="width: 80px;"></td>
		</tr></table><br>
	</div>
</div>

{submit value="Complete First Time Setup"}
