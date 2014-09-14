<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

    require_once '../../functions/functions.php';
    
    $pickupppoints = c2r_pickuppoints_get(0, $_GET['lid']);
    echo json_encode($pickupppoints);
?>