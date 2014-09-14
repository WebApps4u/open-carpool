<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

require_once '../../functions/functions.php'; 
$type = $_GET['type'];
$sel = null;
if ($asel = $_GET['sel']) {
	$sel = $asel;
}
if ($_GET['time']) {
	$steptime = $_GET['time'];
} else {
	$steptime = '00:00';
}
$count = $_GET['count'];
if ($type=='sp') {
	$text = 'Start Point';
	$name = 'start_point';
}
if ($type=='ep') {
	$text = 'End Point';
	$name = 'end_point';
}
if ($type=='rp') {
	$text = 'Route Point';
	$name = 'point[]';
}
if ($type!='rp') {
  /*
  <label for="<?php echo $name ?>"><?php echo $text ?></label>
  */
?>
<?php 
}

$lid = $_GET['lid'];
if (!isset($_GET['rp_steptime'])) echo c2r_getHmtlSelectPoints($name, $lid, $sel, 'class="form-control"');
?>
<?php if (isset($_GET['rp_steptime'])) {
?>
 <input type="text" name="steptime[]" value="<?php echo $steptime ?>" class="form-control" />
 <!-- <a href="#" onclick="delete_point(<?php echo $count ?>)">X</a> //-->
<?php
} ?>
