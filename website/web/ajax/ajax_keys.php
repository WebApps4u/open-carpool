<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

require_once '../../functions/functions.php'; ?>
<?php
$uid = $_GET['uid'];
$lid = $_GET['lid'];
$rid = $_GET['rid'];

$routes = c2r_routes_get(0, $lid, $uid);
$key_array_used = array();
foreach ($routes as $key => $route) {
	if ($rid != $route->id) {
		$key_array_used[$route->key] = $route->key;
	}
}
$free_keys = array();

for ($i=($uid?90:10); $i < ($uid?100:90); $i++) {
	if (!in_array($i, $key_array_used)) {
		$obj = null;
		$obj->name = $i;
		$free_keys[$i] = $obj;
	}
}
$selected = null;
if ($rid) {
	$xroute = $routes[$rid];
	$xkey = $xroute->key;
	// select if route is in this location
	if ($lid == $xroute->lid)
		$selected = $xkey;
}
echo c2r_getHtmlSelect('key', $free_keys, 'name', $selected, 'class="form-control"');
?>