<?php

function smarty_function_t($params, Smarty_Internal_Template $template) {
  return translate($params['t']);
}