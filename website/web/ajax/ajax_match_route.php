<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

    require_once '../../functions/functions.php';
    
    
$uid = $_GET['uid'];


echo json_encode(c2r_get_notification($uid))

//$arr = array ('match'=>$match,'longitude'=>$lon,'latitude'=>$lat,'uid'=>$uid);
//echo json_encode($arr);
?>