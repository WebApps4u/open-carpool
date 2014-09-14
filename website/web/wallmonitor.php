<?php

require_once '../functions/functions.php';

/* only show wall monitor if user is logged in 
 * (we don't know for which company to display offers otherwise) */
if (!$user->group_id) {
    header("Location: /index.php");
} else {
    smarty_display('wallmonitor');    
}

