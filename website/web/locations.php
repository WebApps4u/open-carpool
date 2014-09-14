<?php

require_once '../functions/functions.php';

checkMinGroup(1);

// if not super admin, only show locations for company
$cid_for_loc = 0;
if ($user->group_id <3) {
    $cid_for_loc = $user->company_id;
}
$locations = c2r_locations_get(0, $cid_for_loc);
$companies = c2r_companies_get();

/* Insert new company */
if (isset($_POST['do']) && $_POST['do']=='insert') {
	$name = $_POST['name'];
	$cid = $_POST['cid'];
	$timezone = $_POST['timezone'];
	$phone = $_POST['phone'];
    if ($user->group_id <3) {
        if ($cid != $user->company_id) {
            die('You can\'t insert a location for this company!');
        }
    }
	c2r_locations_insert($name, $cid, $timezone, $phone);
  $locations = c2r_locations_get(0, $cid_for_loc);
}

/* Update company */
if (isset($_POST['do']) && $_POST['do']=='update') {
	$lname = $_POST['name'];
	$lid = $_POST['lid'];
	$cid = $_POST['cid'];
	$timezone = $_POST['timezone'];
	$phone = $_POST['phone'];
    if ($user->group_id <3) {
        if ($cid != $user->company_id) {
            die('You can\'t update a location for this company!');
        }
    }
	c2r_locations_update($lid, $lname, $cid, $timezone, $phone);
	addInfoMessage('Location updated.');
  $locations = c2r_locations_get(0, $cid_for_loc);
}

/* Delete company */
if (isset($_GET['do']) && $_GET['do']=='delete') {
	$lid = $_GET['lid'];
    if ($user->group_id < 3) {
        if ($locations[$lid]->cid != $user->company_id) {
            die('You can\'t delete a location for this company!');
        }
    }
	c2r_locations_delete($lid);
    $locations = c2r_locations_get(0, $cid_for_loc);
}

/* Edit company */
if (isset($_GET['do']) && $_GET['do']=='edit') {
	$edit_lid = $_GET['lid'];
}

$smarty->assign('edit_lid', $edit_lid);
$smarty->assign('locations', $locations);
$smarty->assign('companies', $companies);
smarty_display('locations');