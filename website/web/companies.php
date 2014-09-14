<?php

require_once '../functions/functions.php';

$mingroup = 3;
$bycompany = false;

if ($user->group_id < 3 && ( (isset($_GET['do']) && $_GET['do']=='edit') || (isset($_POST['do']) && $_POST['do']=='update'))) {
  $mingroup = 2;
  $bycompany = true;
}

checkMinGroup($mingroup);

$companies = c2r_companies_get();

/* Insert new company */
if (isset($_POST['do']) && $_POST['do']=='insert') {
	$name = $_POST['name'];
	$smskey = $_POST['smskey'];
	$zendeskid = $_POST['zendeskid'];
	$logourl = $_POST['logourl'];
	$email = $_POST['email'];
	c2r_companies_insert($name, $smskey, $zendeskid, $logourl, $email);
	addInfoMessage("New Company $name inserted");
}

/* Update company */
if (isset($_POST['do']) && $_POST['do']=='update') {
  $cid = $_POST['cid'];
  // just edit own if not an (super?) admin
	if ($bycompany && $companies[$cid]->id != $user->company_id) {
	  checkMinGroup(0);
	}
	$name = $_POST['name'];
	$smskey = $_POST['smskey'];
	$zendeskid = $_POST['zendeskid'];
	$logourl = $_POST['logourl'];
	$email = $_POST['email'];
	$cid = $_POST['cid'];
	$edit_cid = $cid;
	c2r_companies_update($cid, $name, $smskey, $zendeskid, $logourl, $email);
	addInfoMessage("Company $name updated");
}

/* Delete company */
if (isset($_GET['do']) && $_GET['do']=='delete') {
	$cid = $_GET['cid'];
	c2r_companies_delete($cid);
	addInfoMessage("Company deleted");
}


/* Edit company */
if (isset($_GET['do']) && $_GET['do']=='edit') {
	$edit_cid = $_GET['cid'];
  // just edit own if not an (super?) admin
	if ($bycompany && $companies[$edit_cid]->id != $user->company_id) {
	  checkMinGroup(0);
	}
}

/* Reload */
$companies = c2r_companies_get();


$smarty->assign('bycompany', $bycompany);
$smarty->assign('edit_cid', $edit_cid);
$smarty->assign('companies', $companies);
smarty_display('companies');