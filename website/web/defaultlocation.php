<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);

/* get all locations */
$locations = c2r_locations_own();

/* Update default location */
if ($dlid = $_POST['dlid']) {
  c2r_user_update_default_location($user->id, $dlid);
  addInfoMessage(t('updated your location'));
  getUser(); // refresh user
}

/* Display */
$smarty->assign('locations', $locations);
smarty_display('defaultlocation');
?>