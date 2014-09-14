<?php

require_once '../functions/functions.php';

checkMinGroup(1);

if (isset($_GET['close_id']) && $close_id = $_GET['close_id']) {
  c2r_close_single_request($close_id);
  addInfoMessage(t('Your request was closed'));
}

if(isset($_GET['action']) && $_GET['action'] == 'accept_offer') {
  c2r_accept_offer($_GET['driver_id'], $_GET['offer_id'], $_GET['phone_number']);
  addInfoMessage(t('Anjenommen'));
}

$requests = c2r_requests_get($user->id, 'open', 0);	
// Sort by earliest start date
usort($requests, 'sort_requests');
	
$smarty->assign('requests', $requests);
smarty_display('matchingoffers');