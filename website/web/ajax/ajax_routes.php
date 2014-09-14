<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

require_once '../../functions/functions.php';

$lid = $_GET['lid'];
$routes = c2r_routes_get(0, $lid, 0, true, 0, ($_GET['onlyforuser'] ? $user->id : 0));

// Sort Array by Route Name
usort($routes, "sort_by_route_names");

function sort_by_route_names($a, $b) {
    if ($a->name == $b->name) {
      return 0;
    }
    return ($a->name < $b->name) ? -1 : 1;
};

$r2 = array();
foreach($routes as $r) {
  $r2[$r->id] = $r;
}
$routes = $r2;

foreach ($routes as $key => $r) {
	$rps = c2r_route_points_get($r->id);
	$rp1 = array_shift($rps);
	$rp2 = array_pop($rps);
	$p1 = c2r_pickuppoints_get($rp1->point_id);
	$p2 = c2r_pickuppoints_get($rp2->point_id);
	$routes[$key]->name .= " (".$p1->name." - ".$p2->name.")";
	$routes[$key]->name = "#".$r->key." ".$routes[$key]->name;
}

$oc = ($_GET['add_map']=1)?'onchange="loadmap()"':'';
echo c2r_getHtmlSelect('route_id', $routes, 'name', null, $oc.' class="form-control"');
echo '<script>loadmap();</script>';
?>