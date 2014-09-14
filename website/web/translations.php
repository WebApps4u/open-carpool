<?php

require_once '../functions/functions.php';

if ($user->id && in_array($user->id, $ocp_allowed_user_for_translation)) {
  # access allowed
} else {
 checkMinGroup(3);
}

if (is_array($_POST) && count($_POST)) {
  foreach($_POST as $k=>$v) {
    if (preg_match('#translation_(\d+)#', $k, $matches)) {
      $id = $matches[1];
      updateTranslation($id, $v);
    }
  }
  addInfoMessage('Translations updated.');
}

$translations = getAllTranslations($user->ui->language);

$smarty->assign('translations', $translations);
smarty_display('translations');
