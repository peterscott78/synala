
<h1>First Time Setup - Step 5 / 5</h1>

{form action="admin/index"}
<input type="hidden" name="_setup_step" value="5">

<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Crontab Jobs</h3><br /><br />
	</div>

	<p>To complete setup, you must setup the following two crontab jobs.  If you are unsure how to do this, please 
	contact your web host / server administrator.</p>

<pre>
*/5 * * * * cd {$site_path}/data/cron; /usr/bin/php -q check_block.php
*/30 * * * * cd {$site_path}/data/cron; /usr/bin/php -q exchange_rates.php
1 */6 * * * cd {$site_path}/data/cron; /usr/bin/php -q backup.php
</pre><br>

	<p>Once the crontab jobs have been added, setup will be complete.  You may now continue to the <a href="{$site_uri}/admin/">Home Page</a> of the administration panel to continue using Synala.<br>
</div>

{submit value="Complete First Time Setup"}
