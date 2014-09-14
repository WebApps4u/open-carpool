<?php

require_once '../functions/functions.php';

checkMinGroup(2);

$companies = c2r_companies_get();

$smarty->assign('companies', $companies);
smarty_display('configuration');