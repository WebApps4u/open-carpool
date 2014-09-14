<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);

$lid = $user->default_location_id;
$routes = c2r_routes_get(0, $lid, 0, true);

$smarty->assign('routes', $routes);
smarty_display('allroutes');