<html>
<head>
	<title>{$page_title} - {$config['site_name']}</title>
	<link href="{$theme_uri}/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="{$theme_uri}/css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div class="wrapper">

<div class="page-header">

	<div class="navbar">
		<ul class="nav">
			<li><a href="{$site_uri}/index">HOME</a></li>
			<li><a href="{$site_uri}/pay">PAYMENT</a></li>
			<li><a href="{$site_uri}/products">PRODUCTS</a></li>

			{if {$is_login} eq true}
				<li><a href="{$site_uri}/account">MY ACCOUNT</a></li>
				<li><a href="{$site_uri}/logout">LOGOUT</a></li>
			{else}
				<li><a href="{$site_uri}/login">LOGIN</a></li>
				<li><a href="{$site_uri}/register">REGISTER</a></li>
			{/if}
		</ul>
	</div>
	<h2>{$config['site_name']}</h2>
</div>

<div class="container" style="margin-top: 10px; padding-bottom: 120px;">

{$user_message}

<h2>{$page_title}</h2>

