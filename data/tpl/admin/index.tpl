
<h1>Welcome to the Admin Panel</h1>

<div class="row">
	<div class="col-md-8">
		<div class="box">
			<div class="box-header with-border">
				<h3 class="box-title">Wallets</h3>
			</div>

			<div style="margin: 8px;">
				{table alias="coin_wallets" no_checkbox="1"}<br>
			</div>
		</div>

		<div class="box">
			<div class="box-header with-border">
				<h3 class="box-title">Revenue</h3>
			</div>

			<div style="margin: 8px;">
				<canvas id="revenueChart" height="250"></canvas>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="box">
			<div class="box-header with-border">
				<h3 class="box-title">Activity</h3>
			</div>

			<div style="margin: 8px;">

				<div class="info-box bg-green">
					<span class="info-box-icon"><i class="fa fa-btc"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Funds Received</span>
						<span class="info-box-number">{$funds_received} BTC</span>
						<div class="progress">
							<div class="progress-bar" style="width: 0%;"></div>
						</div>
						<span class="progress-description">{$new_deposits} ({$new_deposits_amount} BTC) new deposits</span>
					</div>
				</div>

				<div class="info-box bg-light-blue">
					<span class="info-box-icon"><i class="fa fa-user"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Users</span>
						<span class="info-box-number">{$total_users}</span>
						<div class="progress">
							<div class="progress-bar" style="width: 0%;"></div>
						</div>
						<span class="progress-description">{$new_users} new users</span>
					</div>
				</div>

				<div class="info-box bg-red">
					<span class="info-box-icon"><i class="fa fa-shield"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Products Ordered</span>
						<span class="info-box-number">{$total_products_amount} BTC ({$total_products})</span>
						<div class="progress">
							<div class="progress-bar" style="width: 0%;"></div>
						</div>
						<span class="progress-description">{$new_products} ({$new_products_amount} BTC) new orders</span>
					</div>
				</div>

				<div class="info-box bg-orange">
					<span class="info-box-icon"><i class="fa fa-file-pdf-o"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Invoices Paid</span>
						<span class="info-box-number">{$total_invoices_amount} BTC ({$total_invoices})</span>
						<div class="progress">
							<div class="progress-bar" style="width: 0%;"></div>
						</div>
						<span class="progress-description">{$new_invoices} ({$new_invoices_amount} BTC) new invoices</span>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

