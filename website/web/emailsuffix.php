<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(2);

/* Edit */
$edit_suffix = null;
if (isset($_GET['do']) && $_GET['do'] == 'edit' && $id = $_GET['id']) {
  $edit_suffix = c2r_get_emailsuffix($id);
}

/* Update */
if (isset($_POST['do']) && $_POST['do'] == 'update') {
  c2r_update_emailsuffix(
    $_POST['id'],
    $_POST['suffix'],
    $_POST['location_id']
  );
  addInfoMessage($_POST['suffix'].' updated!');
}

/* Delete */
if (isset($_GET['do']) && $_GET['do'] == 'delete' && $id = $_GET['id']) {
  c2r_delete_emailsuffix($id);
  addInfoMessage('Email suffix deleted!');
}

/* Insert */
if (isset($_POST['do']) && $_POST['do'] == 'insert') {
  c2r_insert_emailsuffix(
    $_POST['suffix'],
    $_POST['location_id']
  );
  addInfoMessage('New email suffix inserted!');
}


$company_id = $user->group_id == 3 ? 0 : $user->company_id;

$emailsuffixes = c2r_get_emailsuffixes($company_id);
$locations = c2r_locations_get(0, $company_id);
#print_r($locations);

$smarty->assign('edit_suffix', $edit_suffix);
$smarty->assign('emailsuffixes', $emailsuffixes);
$smarty->assign('locations', $locations);
smarty_display('emailsuffix');