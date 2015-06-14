<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Synala - Admin Panel</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Bootstrap 3.3.2 -->
    <link href="{$theme_uri}/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />

    <!-- Ionicons -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme style -->
    <link href="{$theme_uri}/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <link href="{$theme_uri}/dist/css/skins/skin-blue.min.css" rel="stylesheet" type="text/css" />
    <link href="{$theme_uri}/dist/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="{$theme_uri}/plugins/iCheck/square/aero.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body class="skin-blue">
    <div class="wrapper">

      <!-- Main Header -->
      <header class="main-header">

        <!-- Logo -->
        <a href="{$site_uri}/admin/index" class="logo"><b>Synala</b></a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <span style="color: #fff; margin-left: 15px; position: absolute; bottom: 15px;">BTC Rate: {$exchange_rate}</span>
          <!-- Navbar Right Menu -->
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

              <!-- Notifications Menu -->
              <li class="dropdown notifications-menu">
                <!-- Menu toggle button -->
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bell-o"></i>
                  <span class="label label-success">{$total_alerts}</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have {$total_alerts} notifications</li>
                  <li>
                    <!-- Inner Menu: contains the notifications -->
                    <ul class="menu">
                      {section name=item loop=$alerts}
                      <li>
                        <a href="{$site_uri}/admin/alerts"><i class="fa {$alerts[item].icon}"></i> {$alerts[item].name}</a>
                      </li>
                      {/section}
                    </ul>
                  </li>
                  <li class="footer"><a href="{$site_uri}/admin/alerts?clearall=1">Clear all alerts</a></li>
                </ul>
              </li>

              <!-- User Account Menu -->
              <li class="dropdown user user-menu">
                <!-- Menu Toggle Button -->
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <!-- The user image in the navbar-->
                  <img src="{$theme_uri}/dist/img/boxed-bg.jpg" class="user-image" alt="User Image"/>
                  <!-- hidden-xs hides the username on small devices so only the image appears. -->
                  <span class="hidden-xs">{$username}</span>
                </a>
                <ul class="dropdown-menu">
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="{$site_uri}/admin/user/manage2?username={$username}" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="{$site_uri}/admin/logout" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

          <!-- Sidebar Menu -->
          <ul class="sidebar-menu">
            <li class="header">Navigation</li>

            {if {$is_login} eq true}
            <!-- Optionally, you can add icons to the links -->
            <li class="treeview">
              <a href="#"><i class="fa fa-fw fa-cog"></i> <span>Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="{$site_uri}/admin/setup/general">General</a></li>
                <li><a href="{$site_uri}/admin/setup/wallets">Wallets</a></li>
                <li><a href="{$site_uri}/admin/setup/products">Products</a></li>
                <li><a href="{$site_uri}/admin/setup/notifications">Notifications</a></li>
              </ul>
            </li>
            <li class="treeview">
              <a href="#"><i class="fa fa-fw fa-users"></i> <span>Users</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="{$site_uri}/admin/user/create">Create New User</a></li>
                <li><a href="{$site_uri}/admin/user/manage">Manager User</a></li>
                <li><a href="{$site_uri}/admin/user/viewall">View All Users</a></li>
              </ul>
            </li>
            <li class="treeview">
              <a href="#"><i class="fa fa-fw fa-money"></i> <span>Financial</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="{$site_uri}/admin/financial/addresses">Addresses</a></li>
                <li><a href="{$site_uri}/admin/financial/transactions">Transactions</a></li>
                <li><a href="{$site_uri}/admin/financial/send_funds">Send Funds</a></li>
                <li><a href="{$site_uri}/admin/financial/outstanding">Outstanding Items</a></li>
                <li><a href="{$site_uri}/admin/financial/invoices">Manage Invoices</a></li>
                <li><a href="{$site_uri}/admin/financial/transfer_wallet">Transfer Wallet</a></li>
              </ul>
            </li>
            <li><a href="{$site_uri}/admin/user_manual"><i class="fa fa-fw fa-question-circle"></i> <span>User Manual</span></a></li>
            {/if}

          </ul><!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>{$page_title}</h1>

          <!--
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
          </ol>
          -->
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">

              {$user_message}


          
