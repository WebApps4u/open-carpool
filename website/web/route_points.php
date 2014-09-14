<?php

require_once '../functions/functions.php';

$rid = $_GET['rid'];
if (!$rid) {
	$rid = $_POST['rid'];
}
$route = c2r_routes_get($rid);
$locations = c2r_locations_get();
$location = $locations[$route->lid];
#$company = c2r_companies_get($location->cid);

if($route->user_id==$user->id) {
	checkMinGroup(1);
	$myroutes = true;
} else {
  if ($location->cid == $user->company_id) {
    checkMinGroup(2);	
  } else {
   checkMinGroup(3);	 
  }
}

// Submit
if (!$_GET['rid']) {
	$rid = $_POST['rid'];	
	c2r_routepoints_delete($rid);
	$pos = 0;
	for ($i=0; $i < 10; $i++) { 
		if (!$_POST['delete_'.$i]) {
			$pos++;
			$pid = $_POST['point'][$i];
			$st = $_POST['steptime'][$i];
			c2r_routepoints_insert($rid, $pid, $st, $pos);
		}
	}
	addInfoMessage('Route Points saved!');
	// Reload route
	$route = c2r_routes_get($rid);
}

$rps = c2r_route_points_get($rid);

if ($_GET['my']) {
  $smarty->assign('my', true);
}

$smarty->assign('rid', $rid);
$smarty->assign('rps', $rps);
$smarty->assign('locations', $locations);
$smarty->assign('location', $location);
$smarty->assign('route', $route);
#$smarty->assign('company', $company);
smarty_display('route_points');