<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);

smarty_display('driver');