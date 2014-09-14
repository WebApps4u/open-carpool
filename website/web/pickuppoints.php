<?php

require_once '../functions/functions.php';
checkMinGroup(2);

/* Insert new Point */
if (isset($_POST['do']) && $_POST['do']=='insert') {
	$name = $_POST['name'];
	$lid = $_POST['lid'];
	$geo = $_POST['geo'];
	$key = $_POST['key'];
	c2r_pickuppoints_insert($name, $lid, $geo, $key);
}

/* Update Point */
if (isset($_POST['do']) && $_POST['do']=='update') {
	$pid = $_POST['pid'];
	$name = $_POST['name'];
	$lid = $_POST['lid_e'];
	$geo = $_POST['geo_e'];
	$key = $_POST['key'];

	c2r_pickuppoints_update($pid, $name, $lid, $geo, $key);
	addInfoMessage(t('Pick-up point ').$name.t(' updated'));
}

/* Delete Pickuppoint */
if (isset($_GET['do']) && $_GET['do']=='delete') {
	$pid = $_GET['pid'];
	c2r_pickuppoints_delete($pid);
}

/* Edit company */
if (isset($_GET['do']) && $_GET['do']=='edit') {
	$edit_pid = $_GET['pid'];
}


$cid_for_loc = 0;
if ($user->group_id < 3) { $cid_for_loc = $user->company_id; }
$locations = c2r_locations_get(0, $cid_for_loc);

$cid_for_pp = 0;
if ($user->group_id < 3) { $cid_for_pp = $user->company_id; }
$pickuppoints = c2r_pickuppoints_get(0, 0, $cid_for_pp);

$smarty->assign('edit_pid', $edit_pid);
$smarty->assign('pickuppoints', $pickuppoints);
$smarty->assign('locations', $locations);
smarty_display('pickuppoints');