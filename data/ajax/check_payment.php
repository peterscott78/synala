<?php

// Initialize
global $ajax;

// Expire needed payments
DB::query("UPDATE coin_pending_payment SET status = 'expired' WHERE expire_time < %d", time());

// Check payment
if (!$row = DB::queryFirstRow("SELECT * FROM coin_pending_payment WHERE pay_hash = %s", $_REQUEST['pay_hash'])) { 
	echo json_encode(array('pay_status' => 'error', 'message' => 'Payment hash does not exist.'));
}

// Send response
$response = array(
	'pay_status' => $row['status']
);
echo json_encode($response);
exit(0);

?>