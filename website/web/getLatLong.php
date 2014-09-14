<?php
require_once '../functions/functions.php';
/* Check User rights */
checkMinGroup(1);
$lid = $user->default_location_id;
$rid = $_GET['rid'];


//$routes = $routes = c2r_routes_get(0, $lid, 0, true);
//echo "<pre>";print_r($routes);echo "</pre>";
//echo "<br>done";
//$locations = c2r_locations_get($rid);
//echo "<pre>";print_r($locations);echo "</pre>";
//echo "<br>done";
//$locations = c2r_routes_get();
//echo "<pre>";print_r($locations);echo "</pre>";
//echo "<br>done";
//$locations = c2r_route_points_get($rid);
//echo "<pre>";print_r($locations);echo "</pre>";
//echo "<br>done";
//$locations = c2r_pickuppoints_get();
//echo "<pre>";print_r($locations);echo "</pre>";
//echo "<br>done";
//echo "<br>done";



$routes = c2r_routes_get();
foreach ($routes as $key => $r) {
	$rps = c2r_route_points_get($r->id);
	$rp2 = array_pop($rps);
	$p2 = c2r_pickuppoints_get($rp2->point_id);
    echo $r->id." -> ".$p2->geo."<br>";
}