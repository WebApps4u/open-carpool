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
if ($_GET['all']==1) {
	$request_uid = 0;
}
$status = $_GET['status'];

if ($close_id = $_GET['close_id']) {
  c2r_close_single_offer($close_id);
  addInfoMessage(t('Your offer was closed'));
}

$offers = c2r_offers_get($request_uid, $status, 0, $filter); // only open offers not in the past
// Sort by earliest start date
usort($offers, 'sort_offers');

$smarty->assign('offers', $offers);
$smarty->assign('status', $status);
$smarty->assign('all', $_GET['all']);
smarty_display('offers');