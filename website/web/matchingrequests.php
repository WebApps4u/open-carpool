<?php

require_once '../functions/functions.php';

checkMinGroup(1);

if ($close_id = $_GET['close_id']) {
  c2r_close_single_offer($close_id);
  addInfoMessage(t('Your offer was closed'));
}

$offers = c2r_offers_get($user->id, 'open', 0);	

// Sort by earliest start date
usort($offers, 'sort_offers');
	
$smarty->assign('offers', $offers);
smarty_display('matchingrequests');