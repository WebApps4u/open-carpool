<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);

if ($_GET['do'] == 'offerride') {
	$time = checkTime($_GET['time']);
	$time_ok = true;
	if (!only_digits($time)) {
		$time_ok = false;
		$e_msg = t('Time: ').$time;
		addErrorMessage($e_msg);
		c2r_log_error('Offer time format', $e_msg.' - '.$_GET['time']);
	}
	$route = c2r_routes_get($_GET['route_id']);
	$sdate = explode('-', $_GET['start_date']);
	$sdate = join('', $sdate);
	if (!count($errors)) {
		$result = c2r_offer_ride($_GET['user_number_id'], $_GET['location_id'], $route->key, $time, $_GET['reverse'], $sdate);
		if ($result && substr($result, 0, 5)!='ERROR') {
			addInfoMessage($result);
			header("Location: ".OCP_BASE_URL."matchingrequests.php"); /* Redirect browser */
    	exit;
		} else {
			$e_msg = $result?$result:t('An error occurred');
			addErrorMessage($e_msg);
			c2r_log_error('Offer time format', $e_msg);
		}
	}
}

$locations = c2r_locations_get();

$smarty->assign('locations', $locations);
smarty_display('offer');