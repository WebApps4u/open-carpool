<?php

/* Main Functions */
require_once '../functions/functions.php';


$keeponpage = false;

if ($err_code = $_GET['error']) {
	if ($err_code=='login') {
		addErrorMessage(t('Please log in or register.'));
	}
	if ($err_code=='rights') {
		addErrorMessage(t('You don\'t have the right to view this page'));
	}
}


/* Logout */
if ($_GET['do'] == 'logout') {
	killSession();
	addInfoMessage(t('You logged out!'));
}



/* PW Lost */
if ($key = $_GET['pwlost']) {
	check_lost_password($key);
	// if script continues, then key was invalid
	addErrorMessage(t('This key is invalid, please use the password lost function again'));
}

/* Login */
if (isset($_POST['email'])) {
	$email = $_POST['email'];
	$password = $_POST['password'];
	if (!loginUser($email, $password)) {
		addErrorMessage(t('Wrong email address or password'));
	}
}

/* Password lost */
if (isset($_POST['lost_email'])) {
	$email = $_POST['lost_email'];
	$keeponpage = true;
	$res = c2r_lost_password($email);
	if ($res==1) {
		addInfoMessage(t('We sent you a login link via email. Please check your email account.'));
	} else {
		addErrorMessage(t('Unknown email address. You can register a new account with this email address.'));
	}
}

if (!$keeponpage && $user) {
  header(t('Location: ').OCP_BASE_URL);
  exit;
}

/* template determination */
smarty_display('lostpassword');