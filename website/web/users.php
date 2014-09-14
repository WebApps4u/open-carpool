<?php

require_once '../functions/functions.php';

checkMinGroup(2);

if ($_POST['do']=='insert') {
	$duplicate_email = false;
	$duplicate_number = false;
	$hasr_errors = false;
	if (!$_POST['email']) {
	  $has_errors = true;
	  addErrorMessage('Please enter Email adress.');
	}
	if (!$_POST['name']) {
	  $has_errors = true;
	  addErrorMessage('Please enter name.');
	}
	if (!$_POST['number']) {
	  $has_errors = true;
	  addErrorMessage('Please enter phone number.');
	}
	foreach (c2r_users_get() as $key => $auser) {
		// check mail
		$theuser = c2r_get_user_info_object($auser->id);
		if ($_POST['email']==$theuser->email) {
			$duplicate_email = true;
			$has_errors = true;
		}
		// Numbers
		$numbers = c2r_user_number_get($auser->id);
		foreach ($numbers as $key => $anumber) {
			if ($anumber->number==$_POST['number']) {
				$duplicate_number = true;
				$has_errors = true;
			}
		}
	}
	if (!$has_errors) {
		c2r_userinfo_insert($_POST['name'], $_POST['email'], $_POST['cid'], $_POST['dlid'], $_POST['gid'], $_POST['is_active'], $_POST['number']);
		unset($_POST);
	} else {
		if ($duplicate_email) {
			$e_msg = 'Duplicate Email: '.$_POST['email'];
			addErrorMessage($e_msg);
			c2r_log_error('Add User', $e_msg);
		}
		if ($duplicate_number) {
			$e_msg = 'Duplicate Numbers: '.$_POST['number'];
		  addErrorMessage($e_msg);
			c2r_log_error('Add User', $e_msg);
		}
	}
}

$users = c2r_users_get($user->group_id < 3 ? $user->company_id : 0);
foreach($users as $uid=>$u) {
  $ui = c2r_get_user_info_object($uid);
  $users[$uid]->ui = $ui;
}

$locations = c2r_locations_get(0, $user->group_id < 3 ? $user->company_id : 0);
$companies = c2r_companies_get();

$smarty->assign('companies', $companies);
$smarty->assign('locations', $locations);
$smarty->assign('has_errors', $has_errors);
$smarty->assign('users', $users);
smarty_display('users');