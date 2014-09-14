<?php

require_once '../functions/functions.php';

$smarty->assign('user', $user);
$smarty->assign('title', 'Open CarPool');
$smarty->display('support.tpl');