<?php

  require_once '../functions/functions.php';

  if ($lang = $_GET['lang']) {
    setcookie("language", $lang);
  }
  
  header("Location: ".OCP_BASE_URL); /* Redirect browser */