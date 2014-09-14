<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

require_once '../../functions/functions.php'; ?>
<?php
$lid = $_GET['lid'];
$pid = $_GET['pid'];
$points = c2r_pickuppoints_get(0, $lid);
$key_array_used = array();
foreach ($points as $key => $point) {
	if ($pid != $point->id) {
		$key_array_used[$point->key] = $point->key;
	}
}
$free_keys = array();
for ($i=1; $i < 100; $i++) {
	if (!in_array($i, $key_array_used)) {
		$obj = null;
		$obj->name = sprintf('%02d', $i);
		$free_keys[sprintf('%02d', $i)] = $obj;
	}
}
$selected = null;
if ($pid) {
	$xpoint = $points[$pid];
	$xkey = $xpoint->key;
	// select if route is in this location
	if ($lid == $xpoint->lid)
		$selected = $xkey;
}
echo c2r_getHtmlSelect('key', $free_keys, 'name', $selected, 'class="form-control"');
?>