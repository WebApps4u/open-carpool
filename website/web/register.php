<?php

require_once '../functions/functions.php';

// Start Session
session_start();

// include Functions
require('../functions/text_register.php');

$display = 1;

if ($_POST['step1']=="1") {
    $err = false;
    $email = strtolower($_POST['email']);
    $handynr = $_POST['handynummer'];
    $name = $_POST['name'];
    
    // Check Email
    if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
        $err = true;
        $err_email = true;
        $err_email_text = $t_error_mail;
    } else {
		$sql = "select email from users where email='$email' and is_active=True";
	    $result = query($sql);
	    if (pg_fetch_row($result)) {
			  $err = true;
			  $err_email2 = true;
			  $err_email_text = $t_error_mail2;
		}
	}

    // Check Handy-Nr.
    if(!eregi("^\+[0-9]+$", $handynr)) {
        $err = true;
        $err_handy = true;
        $err_phone_text = $t_error_phone;
    } else {
		$sql = "select number from users u, user_number un where un.is_active=True and u.is_active=True and un.user_id=u.id and number='$handynr'";
	    $result = query($sql);
	    if (pg_fetch_row($result)) {
			$err = true;
			$err_handy2 = true;
			$err_phone_text = $t_error_phone2;
		}	
	}
    
    // Check Name
    if (!$name) {
        $err = true;
        $err_name = true;
        $err_name_text = $t_error_name;
    }

    if (!$err) {
        $display = 2;    
       // Create and send code
       if ($_SESSION['codes']!="$email$handynr$name") {
         $codes = getCodes($email, $handynr, $name);
         $_SESSION['codes'] = "$email$handynr$name";
       }
    }
}

if (isset($_GET['code']) || isset($_POST['code'])) {
    $code = $_POST['code'] ? $_POST['code'] : $_GET['code'];
    // Check Code
    if (!checkEmailCode($code)) {
        $err = TRUE;
        $display = 5;
    }
    if (!$err) {
        $display = 3;
        $first = $_GET['code'];
        if (!$first) {
            $smscode = $_POST['smscode'];
            if (!$code || !$smscode || !checkSMSCode($code, $smscode)) {
                $err = true;
                $err_smscode = true;
                $err_code_text = $t_error_code;
            } else {
              // registered Succesfully;
              header("Location: ".OCP_BASE_URL."profile.php?registered=1"); /* Redirect browser */
            	exit;
            }
        }
    }
}


$smarty->assign('display', $display);
$smarty->assign('disclaimer', $disclaimer);
$smarty->assign('t_intro', $t_intro);
$smarty->assign('t_error', $t_error);
$smarty->assign('t_name', $t_name);
$smarty->assign('t_email', $t_email);
$smarty->assign('t_phone', $t_phone);
$smarty->assign('t_submit', $t_submit);
$smarty->assign('err_name_text', $err_name_text);
$smarty->assign('err_email_text', $err_email_text);
$smarty->assign('err_phone_text', $err_phone_text);
$smarty->assign('err_code_text', $err_code_text);
$smarty->assign('t_thankyou', str_replace("%email%", $email, $t_thankyou));
$smarty->assign('t_entercode', $t_entercode);
$smarty->assign('t_entercode2', $t_entercode2);
$smarty->assign('t_confirmcode', $t_confirmcode);
$smarty->assign('t_finish', $t_finish);
$smarty->assign('t_error_code', $t_error_code);
$smarty->assign('code', $code);
smarty_display('register');