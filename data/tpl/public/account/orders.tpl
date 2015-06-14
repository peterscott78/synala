
<h1>Orders / Invoices</h1>

<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Orders</a></li>
		<li><a href="#tab2" data-toggle="tab">Invoices</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Orders</h3><br /><br />

				<p>Below shows all orders on your account, which you have previously purchased.  You may view full details on any order by clicking the desired <i>Manage</i> button below.</p>
			</div>

			{if {$has_orders} eq true}
				{table alias="orders" userid="~userid~" status="pending"}
			{else}
				<center><p><b>You have no previous orders.</b></p></center>
			{/if}

		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Invoices</h3><br /><br />

				<p>Below shows all invoices on your account.  You may view full details and/or make payment by clicking the desired button below.</p>
			</div>

			{if {$has_invoices} eq true}
				{table alias="invoices" userid="~userid~" status="pending"}
			{else}
				<center><p><b>You have no previous invoices.</b></p></center>
			{/if}
		</div>

	</div>
</div>
