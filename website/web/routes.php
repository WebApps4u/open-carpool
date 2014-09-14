<?php

require_once '../functions/functions.php';

$myroutes = false;
if($_GET['my']==1 || $_POST['my']==1) {
	checkMinGroup(1);
	$myroutes = true;
} else {
	checkMinGroup(2);	
}

/* Insert new company */
if (isset($_POST['do']) && $_POST['do']=='insert') {
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	$lid = $_POST['lid'];
	$key = $_POST['key'];
	$user_id = $_POST['user_id'];
	if ($user_id=='user_id') {
		$user_id = $user->id;
	}
	if ($user_id=='') {
		$user_id=0;
	}
	$status = $_POST['status'];
	$new_id = c2r_routes_insert($origin, $destination, $status, $key, $lid, $user_id);
	header("Location: ".OCP_BASE_URL."route_points.php?rid=".$new_id.($myroutes?'&my=1':'')); /* Redirect browser */
	exit;
}

/* Update company */
if (isset($_POST['do']) && $_POST['do']=='update') {
	$rid = $_POST['rid'];
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	$lid = $_POST['lid_e'];
	$key = $_POST['key'];
	$user_id = $_POST['user_id'];
	if ($user_id=='user_id') {
		$user_id = $user->id;
	}
	$status = $_POST['status'];

	c2r_routes_update($rid, $origin, $destination, $status, $key, $lid, $user_id);
}

/* Delete Route */
if (isset($_GET['do']) && $_GET['do']=='delete') {
	$rid = $_GET['rid'];
	c2r_routes_delete($rid);
}

/* Edit company */
if (isset($_GET['do']) && $_GET['do']=='edit') {
	$edit_rid = $_GET['rid'];
}

$cid_for_loc = 0;
if ($user->group_id < 3) { $cid_for_loc = $user->company_id; }
$locations = c2r_locations_get(0, $cid_for_loc);

$cid_for_routes = 0;
if ($user->group_id<3) {
    $cid_for_routes = $user->company_id;
}
$routes = c2r_routes_get(0, 0, $myroutes?$user->id:0, false, $cid_for_routes);

$smarty->assign('myroutes', $myroutes);
$smarty->assign('edit_rid', $edit_rid);
$smarty->assign('user_id', $user_id);
$smarty->assign('locations', $locations);
$smarty->assign('routes', $routes);
smarty_display('routes');
