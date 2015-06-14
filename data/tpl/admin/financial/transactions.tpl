
<h1>Payments Received</h1>


<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Received</a></li>
		<li><a href="#tab2" data-toggle="tab">Sent</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
			<div class="box-header with-border">
				<h3 class="box-title">Received</h3><br /><br />

				<p>The below table lists all payments received, starting from the most recent.  You may view details on any transaction by clicking the desired <i>View Tx</i> button.</p>
			</div>

			{table alias="coin_inputs"}
		</div>

		<div class="tab-pane" id="tab2">
			<div class="box-header with-border">
				<h3 class="box-title">Sent</h3><br /><br />

				<p>The below table lists all sends, starting from the most recent.  You may view details on any transaction by clicking the desired <i>View Tx</i> button.</p>
			</div>

			{table alias="coin_sends" status="sent"}
		</div>
	</div>
</div>

