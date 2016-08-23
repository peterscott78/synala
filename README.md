Synala
==============

Synala allows anyone to easily and securely accept Bitcoin payments online with virtually no fees (~0.0001 BTC to send funds), and with no middle man, ensuring you always retain 100% control over all funds.  This is an alternative to using the merchant services available, which charge fees in order to accept payments, and hold your funds in their wallets instead of your own.

If you need an easy to use, secure, quality online wallet to accept payments from customers / clients, then Synala is for you.  It fully supports multiple BIP32 wallets (standard and multi-sig), offline signing, user registration, invoices, products, and more.

## Changelog

* v0.3 Released -- Please check our [blog post](http://envrin.com/blog/synala_0_3_upgrade) for full details.


## Requirements

* LINUX server / VPS capable of running Bitcoin Core
* Bitcoin Core v0.10.0+
* One (1) clean mySQL database
* ~15MB of HD space


## Installation

Installation is extremely simple.  Download the archive of Synala, unzip it, and upload the contents to your server.  Once uploaded, simply open it in your web browser, and you will be prompted with a setup screen.  Follow the setup instructions, and once complete you will be ready to begin accepting Bitcoin payments.

Synala does require Bitcoin Core on your server.  Please ensure you download a copy of the latest version from the [Bitcoin.Org Download Page](https://bitcoin.org/en/download), and upload the bin/bitcoind and bin/bitcoin-cli files to your server.  The Synala installation wizard will provide you with a sample bitcoin.conf file to use for Synala.

**NOTE:** Synala only uses Bitcoin Core to watch txs flow through the blockchain, and to broadcast sends.  No funds are actually stored within the wallet.dat file, and all other actions (address generation, creating / signing txs) occur in-house within Synala.


##### Nginx Configuration

If you are using Nginx instead of Apache, you must add a new <i>location</i> directive to your Nginx configuration file.  For example, if you uploaded the system into the /synala/ directory of your server, you would add the directive:

```
location /synala {
	root    /home/username/public_html/synala
	index   index.php;
	send_timeout 180;
	proxy_read_timeout 120;
	proxy_connect_timeout 120;
	try_files $uri $uri/ /synala/index.php?route=$uri&$args;
}
```

Notice the three occurences of **/synala** above, and ensure they're correct within your Nginx configuration.  Once the change has been made, restart Nginx, and everything should begin working properly.


## Templates

Synala uses the Smarty template engine, and comes with a small public web site as well as an administration panel.  The public site can be easily modified, pages added, and more.  The actual theme / skin of the public site (header, footer, CSS, Javascript, images) can be found within the ***/themes/public/*** directory.  The header.tpl and footer.tpl files are automatically displayed as the header and footer of every page.

There are a few special template variables that can be used within any page, and are explained below:

Variable | Notes
-------- | -----
{$theme_uri} | Is the relative path to the ***/themes/public/*** directory, and is used to link to CSS, Javascript, images, etc.  For example, to link to the file located at ***/themes/public/css/style.css***, you would link to: ***{$theme_uri}/css/style.css***
{$site_uri} | Is the relative path to the installation directory of Synala, and used to link to other pages within the site.  For example, to link to the ***/register*** page of the site, you would use: ***{$site_uri]}/register***


##### Pages

Within the /data/tpl/public/ directory, you will find all the pages contained within the public site.  Synala simply displays the correct template depending on the URI being viewed.  For example, if you installed within the /synala/ directory of your server, and visit the URL http://domain.com/synala/pay, then the pay.tpl template from this directory will be displayed.

To add a new page, simply upload a new .tpl file into the /data/tpl/public/ directory, and the page will be immediately live.  For example, if you wanted to add a page at http://domain.com/synala/about_us, you would simply add an about_us.tpl file to the directory with the desired page contents.


## Hooks

If desired, you may have additional PHP code automatically executed when various actions occur, such as when a new deposit is received, a user registers, and more.  Within the <i>/data/hooks/</i> directory you will see various PHP files, which are executed when the various actions are performed.  The filenames should be quite straight forward.

Within the PHP files, you'll see an empty function.  Add any desired code to this function, and it will be automatically executed every time that action takes place.  One array is passed to the function each time, and below describes the variables available within the array.

##### confirmed_deposit, invoice_paid, new_deposit, product_purchased variables

Variable | Availability | Description
-------- | ------------ | -----------
input_id | All | ID# from the coin_inputs table.
userid   | All | ID# of the user who made the payment.
username | All | Username of the user who made the payment.
wallet_id | All | ID# of the wallet that received the funds.  This is the id column from the coin_wallets table.
product_id | All | If a product purchase, the ID# of the product that was purchased.  Otherwise, 0.
order_id | All | If a product purchase, the ID# of the order.  Otherwise, 0.
invoice_id | All | If an invoice payment, the ID# of the invoice.  Otherwise, 0.
is_confirmed | All | A 1/0, depending whether or not the input has reached the minimum # of confirmations.
is_spent | All | A 1/0, defining whether or not the input has been spent.  This will almost always be 0.
is_change | All | A 1/0, defining whether or not this is a change input.
confirmations | All | Number of confirmations the input has recieved.
blocknum | All | The block# the input was added to.  If unconfirmed, will be 0.
address | All | The address to which funds were received at.
txid | All | The txid of the input.
vout | All | The vout of the input.
amount | All | The amount of the input in BTC.
date_added | All | The date the input was received by the system, formatted in YYYY-MM-DD HH:II:SS
-------- | ------------ | -----------
product_name | product_purchased | Name of the product purchased.
product_description | product_purchased | Description of the product purchased.
product_amount | product_purchased | Amount of the product.
product_currency | product_purchased | Currency of the product, as defined by you within product settings.
-------- | ------------ | -----------
invoice_status | invoice_paid | The status of the invoice (pending, paid, cancelled)
invoice_amount | invoice_paid | Amount in fiat of the invoice.
invoice_amount_btc | invoice_paid | Amount in BTC of the invoice.
invoice_amount_paid | invoice_paid | Amount in BTC that has been paid of the invoice.
invoice_currency | invoice_paid | Currency in which the invoice was generated.
invoice_note | invoice_paid | Any additional note you defined when generating the invoice.


##### funds_sent variables

Variable | Description
-------- | -----------
send_id | Unique ID# of the send.
wallet_id | ID# of the wallet from which funds were sent.  This is the id column of the coin_wallets table.
status | Status of the send (pending, sent, cancelled)
amount | Amount of the send in BTC
txid | Only applicable if status is "sent", and is the txid of the outgoing transaction.
date_added | Date funds were sent, formatted in YYYY-MM-DD HH:II:SS
outputs | An associative array containing all outputs within the send.  Each element is an array that contains an "address" and "amount" variable, denoting how much was sent to each address.


##### new_user variables

Variable | Description
-------- | -----------
userid | Unique ID# of the user.
group_id | Group ID# of the user (1 = administrator, 2 = regular member).
status | Status of the user (active, inactive, deleted).
username | Unique username of the user.
full_name | Full name of the user.
email | E-mail address of the user.
date_created | Date user was created, formatted in YYYY-MM-DD HH:II:SS



## How It Works

Synala uses BIP32 wallets, which allow for the generation of just over 2 billion addresses per-wallet.  Only the public BIP32 keys are stored within the database, and are heavily encrypted with multiple iterations of AES256.

All child keys, addresses, transactions, signatures, and everything else is generated in-house within Synala, without any aid from 3rd party systems such as Bitcoin Core.

Bitcoin Core however is used to watch all transactions flow through the blockchain, broadcast transactions to the blockchain, and retrieve information on any desired transaction.  This is done because Bitcoin Core is still currently the most reliable way to communicate with the P2P network.

When a new address is generated within Synala, it will be imported into Bitcoin Core as a watch-only address, hence triggering the ***walletnotify*** command informing Synala of an incoming transaction.  On top of this, each block is also sent to Synala for processing, all transactions within the block are checked against addresses in Synala, and confirmation numbers are updated appropriately.


