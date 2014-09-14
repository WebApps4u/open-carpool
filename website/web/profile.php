<?php

require_once '../functions/functions.php';

$uid = isset($_GET['uid']) ? $_GET['uid'] : null;
if (!$uid) {
	$uid= isset($_POST['uid']) ? $_POST['uid'] : null;
}
$admin_view = false;
if ($uid) {
	$admin_view = true;
	checkMinGroup(2);
} else {
	$uid = $user->id;
	checkMinGroup(1);
}


if (isset($_POST['new_password'])) {
	$new_password = $_POST['new_password'];
	$retype = $_POST['retype'];
	if ($new_password && $new_password==$retype) {
		c2r_change_password($new_password, $uid);
		addInfoMessage('Password changed!');
	} else {
		addErrorMessage('Passwords do not match!');
	}
}

if (isset($_GET['user_delete']) && $delid=$_GET['user_delete']) {
	c2r_user_delete($delid);
	killSession();
	header("Location: ".OCP_BASE_URL."users.php"); /* Redirect browser */
	addInfoMessage(t('Profile deleted'));
	exit;
}

if ($admin_view) {
	if ($_POST['action']=='update_userinfo') {
		c2r_userinfo_update($uid, $_POST['name'], $_POST['email'], $_POST['cid'], $_POST['dlid'], $_POST['gid'], $_POST['is_active'], $_POST['language']);
		addInfoMessage(t('Profile Data saved'));
	}
	if ($_POST['action']=='new_number') {
		c2r_user_number_add_admin($uid, $_POST['number']);
		addInfoMessage(t('New Phone number added'));
	}
	if ($del_unid=$_GET['number_delete']) {
		c2r_user_number_delete($del_unid);
	}
	if ($dnid = $_POST['default']) {
		c2r_user_number_default($uid, $dnid);
	}
} else {
	if (isset($_POST['validate_unid']) && $vunid=$_POST['validate_unid']) {
		c2r_user_number_activate($vunid, $_POST['code']);
	}
	if (isset($_POST['number']) && $number=$_POST['number']) {
		if(!eregi("^\+[0-9]+$", $number)) {
	        addErrorMessage(t('This phone number has a wrong format. Please use the + sign, followed by country code, area code and your individual number.'));
	    } else {
			$sql = "select number from users u, user_number un where u.is_active=True and un.user_id=u.id and number='$number'";
		    $result = query($sql);
		    if (pg_fetch_row($result)) {
				addErrorMessage('This number is already used!');
			} else {
				c2r_user_number_add($user->id, $number);
				addInfoMessage(t('Please validate ').$number.t('. Enter the Code you get via SMS.'));
			}
		}
		
	}
	if (isset($_GET['number_delete']) && $del_unid=$_GET['number_delete']) {
	  addInfoMessage(t('Phone number deleted'));
		c2r_user_number_delete($del_unid);
	}
	if (isset($_POST['default']) && $dnid = $_POST['default']) {
		c2r_user_number_default($user->id, $dnid);
	}
	if (isset($_POST['dlid']) && ($dlid = $_POST['dlid']) && isset($_POST['language']) && ($lng = $_POST['language'])) {
		c2r_user_update_default_location($user->id, $dlid);
		c2r_user_update_language($user->id, $lng);
		$user->ui->language = $lng;
		addInfoMessage(t('Profile Data updated'));
	}
}

$ui = c2r_get_user_info_object($uid);
$uns = c2r_user_number_get($uid);
$companies = c2r_companies_get();

// if not super admin, only show locations for company
$cid_for_loc = 0;
if ($user->group_id <3) {
    $cid_for_loc = $user->company_id;
}
$locations = c2r_locations_get(0, $cid_for_loc);

$bool_to_activate = false;
foreach ($uns as $key => $un) {
	if ($un->is_active) {
		continue;
	}
	$bool_to_activate = true;
}

// Add Message to change Password
if (isset($_GET['cp'])) {
  addInfoMessage('Please change your Password.');
}

// Add Message to change Password
if (isset($_GET['registered'])) {
  addInfoMessage(t('You are now registered.'));
  addInfoMessage(t('Please change your password now!'));
  addInfoMessage(t('Please check your default location and change it if needed.'));
}

$smarty->assign('user', $user);
$smarty->assign('ui', $ui);
$smarty->assign('uid', $uid);
$smarty->assign('uns', $uns);
$smarty->assign('locations', $locations);
$smarty->assign('admin_view', $admin_view);
$smarty->assign('bool_to_activate', $bool_to_activate);
$smarty->assign('companies', $companies);
smarty_display('profile');