<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);
if($user->group_id ==2) {
  $filter = $user->company_id;
} else {
  $filter = null;
}

$request_uid = $user->id;
$all = isset($_GET['all']) ? $_GET['all'] : '';
if ($all==1) {
	$request_uid = 0;
}
$status = $_GET['status'];

if (isset($_GET['close_id']) && $close_id = $_GET['close_id']) {
  c2r_close_single_request($close_id);
  addInfoMessage('Your request was closed!');
}

$requests = c2r_requests_get($request_uid, $status, 0, $filter); // only requsts not in the past
// Sort by earliest start date
usort($requests, 'sort_requests');

$smarty->assign('requests', $requests);
$smarty->assign('status', $status);
$smarty->assign('all', $all);
smarty_display('requests');