<?php
/*
Open CarPool is a free and open source carpooling/dynamic ride sharing 
system, open to be connected to other car pools and public transport.
Copyright (C) 2009-2014  Oliver Pintat, Clemens Rath

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

In case this software interacts with other software tools, other 
licenses may apply.
*/

require_once('config.php');
require_once('gravatar.php');

// Check, if page is called via https
if (OCP_USE_HTTPS) {
    if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("Location: $redirect");
        exit;
    }
}

if (file_exists('../lib/smarty/Smarty.class.php')) {
  require_once('../lib/smarty/Smarty.class.php');
  /* Prepare Smarty */
  $smarty = new Smarty();
  $smarty->setTemplateDir('../templates/');
  $smarty->setCompileDir('../tmp/templates_c/');
  $smarty->addPluginsDir('../functions');

} else {
  require_once('../../lib/smarty/Smarty.class.php');
  /* Prepare Smarty */
  $smarty = new Smarty();
  $smarty->setTemplateDir('../../templates/');
  $smarty->setCompileDir('../../tmp/templates_c/');
  $smarty->addPluginsDir('../../functions');
}


ini_set("default_socket_timeout",OCP_TIMEOUT);

/* Start session */
session_start();

/* Init User */
getUser();

/* Config Vars from DB */
// SMS Key
define('OCP_SMS_KEY', $user ? $user->ui->company->smskey : '');

// Zen Desk ID
define('OCP_ZENDESKID', ($user && $user->ui->company->zendeskid) ?  $user->ui->company->zendeskid : OCP_ZENDESK_FALLBACK);

// Logo
define('OCP_LOGOURL', ($user && $user->ui->company->logourl) ?  $user->ui->company->logourl : null);

// Language

if (isset($_COOKIE["language"]) && $_COOKIE["language"] && isset($ocp_languages[$_COOKIE["language"]])) {
  define('OCP_LANGUAGE', $_COOKIE["language"]);
} else {
  define('OCP_LANGUAGE', 'en');
}

/* Smarty Display */
function smarty_display($template) {
  global $user, $errors, $msgs, $smarty, $ocp_languages, $ocp_allowed_user_for_translation;
  $smarty->assign('msgs', $_SESSION['msgs']);
  $smarty->assign('errors', $_SESSION['errors']);
  $smarty->assign('user', $user);
  $smarty->assign('zendeskid', OCP_ZENDESKID);
  $smarty->assign('ocp_logourl', OCP_LOGOURL);
  $smarty->assign('ocp_languages', $ocp_languages);
  $smarty->assign('ocp_language', OCP_LANGUAGE);
  $smarty->assign('ocp_allowed_user_for_translation', $ocp_allowed_user_for_translation);
  $smarty->display($template.'.tpl');
  $_SESSION['msgs'] = array();
  $_SESSION['errors'] = array();
}

function addInfoMessage($msg) {
  $_SESSION['msgs'][] = $msg;
}

function addErrorMessage($msg) {
  $_SESSION['errors'][] = $msg;
}

/* Fill User Object */
function getUser() {
	global $user;
	
	$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
	if ($uid) {
		$user_info = c2r_get_user_info($uid);
		$user->id = $user_info[0];
		$user->name = $user_info[1];
		$user->email = $user_info[2];
		$user->company_id = $user_info[3];
		$user->default_location_id = $user_info[4];
		$user->group_id = $user_info[5];
		$user->ui = c2r_get_user_info_object($uid);
		$user->gravatar_big = get_gravatar($user->email, 80);
		$user->gravatar_small = get_gravatar($user->email, 32);
	} else {
		$user = null;
	}
}

function checkTime($value='')
{
	$array = split(':', $value);
	if (count($array)!=2) {
		return "Wrong time format";
	}
	$h = $array[0];
	$m = $array[1];
	if ($h<0 | $h>23) {
		return "Wrong time format";
	}
	if ($m<0 | $m>59) {
		return "Wrong time format";
	}
	return sprintf("%02d", $h).sprintf("%02d", $m);
}

function only_digits ($string) {
	if (ereg_replace("[^0-9]","",$string)==$string) return true;
	return false;
}

function checkMinGroup($min_group_id)
{
	global $user;
	if (!$user) {
		header("Location: ".OCP_BASE_URL."login.php?error=login"); /* Redirect browser */
		exit;
	}
	if ($user->group_id < $min_group_id) {
		header("Location: ".OCP_BASE_URL."login.php?error=rights"); /* Redirect browser */
		exit;
	}
}

function setUser($uid)
{
	$_SESSION['uid'] = $uid;
	getUser();
}

function killSession() {
	global $user;
	
	session_unset();  
	session_destroy();
	$user = null;
}

function check_lost_password($key) {
  $data = '{"method": "check_lost_password", "params": ["'.$key.'"], "id": 0}';
  $uid = post($data);
  if ($uid) {
  	setUser($uid);
	header("Location: ".OCP_BASE_URL."profile.php?cp=1"); /* Redirect browser */
	exit;
  }
  return false;
}

function loginUser($email, $password) {
	$uid = c2r_check_login($email, $password);
	if ($uid) {
		setUser($uid);
		return true;
	} 
	return false;
}

function c2r_timezones_get() {
  $data = '{"method": "get_common_timezones", "params": [], "id": 0}';
  return post($data);
}

function c2r_get_routes() {
  $data = '{"method": "get_routes", "params": [""], "id": 0}';
  post($data);
}

function c2r_get_user_routes($user_id, $location_id) {
  $data = '{"method": "get_user_routes", "params": ['.$user_id.', '.$location_id.'], "id": 0}';
  post($data);
}

function c2r_offer_ride($user_number_id, $location_id, $route_id, $time, $reverse, $start_date, $send_sms=1) {
	if (!$reverse) $reverse = '0';
	$data = '{"method": "offer_ride", "params": ['.$user_number_id.','.$location_id.',"'.$route_id.'","'.$time.'",'.$reverse.',"'.$start_date.'", '.$send_sms.'], "id": 0}';
	return post($data);
}

function c2r_request_ride($user_number_id, $location_id, $start_point, $end_point, $time_earliest, $time_latest, $start_date, $send_sms=0) {
 	$data = '{"method": "request_ride", "params": ['.$user_number_id.','.$location_id.',"'.$start_point.'","'.$end_point.'","'.$time_earliest.'","'.$time_latest.'","'.$start_date.'", '.$send_sms.'], "id": 0}';
 	return post($data);
}

function c2r_request_ride_route($user_number_id, $location_id, $key, $reverse, $time_earliest, $time_latest, $start_date, $send_sms=0) {
	if (!$reverse) $reverse = '0';
 	$data = '{"method": "request_ride_route", "params": ['.$user_number_id.','.$location_id.',"'.$key.'",'.$reverse.',"'.$time_earliest.'","'.$time_latest.'","'.$start_date.'", '.$send_sms.'], "id": 0}';
 	return post($data);
}

function c2r_get_user_number_id_from_email($email) {
  $data = '{"method": "get_user_number_id_from_email", "params": ["'.$email.'"], "id": 0}';
  return post($data);
}

function c2r_check_login($email, $password) {
  $data = '{"method": "check_login", "params": ["'.$email.'","'.$password.'"], "id": 0}';
  return post($data,true);
}

function c2r_get_user_info($id) {
  $data = '{"method": "get_user_info", "params": ['.$id.'], "id": 0}';
  return post($data);
}

function c2r_get_user_info_object($id) {
  $data = '{"method": "get_user_info", "params": ['.$id.'], "id": 0}';
  $r = post($data);
  $u->id = $r[0];
  $u->name = $r[1];
  $u->email = $r[2];
  $u->company_id = $r[3];
  $u->company = c2r_companies_get($u->company_id);
  $u->default_location_id = $r[4];
  $u->default_location = c2r_locations_get($u->default_location_id);
  $u->group_id = $r[5];
  $u->group = c2r_groupnames($u->group_id);
  $u->is_active = $r[6];
  $u->is_active_text = ($r[6]?'Yes':'No');
  $u->language = $r[7];
  $u->gravatar_big = get_gravatar($u->email, 80);
  $u->gravatar_small = get_gravatar($u->email, 32);
  return $u;
}

function c2r_groupnames($id=0)
{
	$gns = array(1 => 'User', 2 => 'Company Admin', 3 => 'Super Admin');
	if ($id) {
		return $gns[$id];
	}
	return $gns;
}

function c2r_groupnames_select($admin_group_id)
{
	$n1->name='User';
	$n2->name='Company Admin';
	$n3->name='Super Admin';
	if ($admin_group_id > 2) {
	  $gns = array(1 => $n1, 2 => $n2, 3 => $n3);
	} else {
	  	$gns = array(1 => $n1, 2 => $n2);
	}

	if ($id) {
		return $gns[$id];
	}
	return $gns;
}


function c2r_yesno()
{
	$n1->name='Yes';
	$n2->name='No';
	$gns = array(1 => $n1, 0 => $n2);
	return $gns;
}

function c2r_get_user_number($user_number_id)
{
  	$data = '{"method": "get_user_number", "params": ['.$user_number_id.'], "id": 0}';
	$r = post($data);
	return $r;
}

function c2r_companies_get($cid=0) {
  if ($cid=='') {
    $cid = 0;
  }

  $data = '{"method": "companies_get", "params": ['.$cid.'], "id": 0}';
  $c1 = post($data);
	$companies = array();
	foreach ($c1 as $key => $value) {
		$company = null;
		$id = $value[0];
		$name = $value[1];
		$smskey = $value[2];
		$zendeskid = $value[3];
		$logourl = $value[4];
		$email = $value[5];
		$company->id = $id;
		$company->name = $name;
		$company->smskey = $smskey;
		$company->zendeskid = $zendeskid;
		$company->logourl = $logourl;
		$company->email = $email;
		$companies[$id] = $company;		
	}
	if ($cid) {
		return $companies[$cid];
	}
	return $companies;
}

function c2r_locations_own() {
  global $user;
  $cid_for_loc = $user->company_id;
  return c2r_locations_get(0, $cid_for_loc);
}

function c2r_locations_get($lid=0, $i_cid=0) {
  $data = '{"method": "locations_get", "params": [], "id": 0}';
  $l1 = post($data);
	$locations = array();
	foreach ($l1 as $key => $value) {
        if ($i_cid && $i_cid != $value[1]) { continue; } 
		$location = null;
		$id = $value[0];
		$cid = $value[1];
		$name = $value[2];
		$timezone = $value[3];
		$phone = $value[4];
		$location->id = $id;
		$location->cid = $cid;
		$location->name = $name;
		$location->timezone = $timezone;
		$location->phone = $phone;
		$locations[$id] = $location;		
	}
	if ($lid) {
		return $locations[$lid];
	}
	return $locations;
}

function c2r_users_get($cid = 0) {
  	$data = '{"method": "users_get", "params": [], "id": 0}';
  	$u1 = post($data);
	$users = array();
	foreach ($u1 as $key => $value) {
		$u = null;
		$id = $value[0];
		$u->id = $id;
		$name = $value[1];
		$u->name = $name;
		if ($cid && $value[4] != $cid) {
      continue;
		}
		$users[$id] = $u;		
	}
	return $users;
}

function c2r_user_get($idx = 0) {
  $data = '{"method": "users_get", "params": [], "id": 0}';
  $u1 = post($data);
	$users = array();
	foreach ($u1 as $key => $value) {
		$u = null;
		$id = $value[0];
		$u->id = $id;
		$name = $value[1];
		$u->name = $name;
		$u->company_id = $value[4];
		if ($value[0] == $idx) {
      return $u;
		}
	}
	return null;
}

function c2r_pickuppoints_get($pid=0, $i_lid=0, $i_cid=0) {
  	$data = '{"method": "pickuppoints_get", "params": [], "id": 0}';
  	$p1 = post($data);
	$pickuppoints = array();
	foreach ($p1 as $key => $value) {
		$point = null;
		$id = $value[0];
		$lid = $value[1];
		$name = $value[2];
		$geo = $value[3];
		$key = $value[4];
		$point->id = $id;
		$point->lid = $lid;
		$point->name = $name;
		$point->geo = $geo;
		$point->key = $key;

        // get location if $i_cid is set
        if ($i_cid) {
            $loc = c2r_locations_get($lid);
            if ($i_cid != $loc->cid) continue;
        }
		if ($i_lid == 0 || $i_lid == $lid) {
			$pickuppoints[$id] = $point;			
		}
	}
	if ($pid) {
		return $pickuppoints[$pid];
	}
	return $pickuppoints;
}

function c2r_routes_get($rid=0, $i_lid=0, $i_user_id=0, $onlyActive=false, $i_cid = 0, $onlyforuser = false) {
  	$data = '{"method": "routes_get", "params": [], "id": 0}';
  	$p1 = post($data);
	$routes = array();
	foreach ($p1 as $key => $value) {
		$route = null;
		$id = $value[0];
		$origin = $value[1];
		$destination = $value[2];
		$status = $value[3];
		$key = $value[4];
		$lid = $value[5];
		$user_id = $value[6];
		
		if ($onlyActive && $status!='enabled') {
			continue;
		}
		
        // get Location
        if ($i_cid) {
            $location = c2r_locations_get($lid);
            if ($location->cid != $i_cid) continue;
        }

		$route->id = $id;
		$route->lid = $lid;
		$route->origin = $origin;
		$route->destination = $destination;
		$route->key = $key;
		$route->user_id = $user_id;
		$route->status = $status;
		$route->name = $route->origin;
		if ($onlyforuser) {

      if ($route->user_id != $onlyforuser && $route->user_id != 0) {
#		    echo "=== $onlyforuser - ".$route->user_id;
        continue;
      }
		}
		if ($i_user_id) {
			if ($route->user_id==$i_user_id) {
				if ($i_lid == 0 || $i_lid == $lid) {
					$routes[$id] = $route;		
				}
			}
		} else { 
			if ($i_lid == 0 || $i_lid == $lid) {
				$routes[$id] = $route;		
			}
		}
	}
	if ($rid) {
		return $routes[$rid];
	}	
	return $routes;
}

function c2r_route_points_get($rid) {
  	$data = '{"method": "route_points_get", "params": ['.$rid.'], "id": 0}';
  	$rps_tmp = post($data);
	$rps = array();
	foreach ($rps_tmp as $key => $value) {
		$rp = null;
		$rp->id = $value[0];
		$rp->route_id = $value[1];
		$rp->point_id = $value[2];
		$rp->steptime = $value[3];
		$rp->position = $value[4];
		$rps[$rp->id] = $rp;
	}
	return $rps;
}

function c2r_user_number_get($user_id, $only_active = false) {
  $data = '{"method": "user_numbers_get", "params": ['.$user_id.'], "id": 0}';
  $rps_tmp = post($data);
	foreach ($rps_tmp as $key => $value) {
	  if ($only_active && !$value[4]) {
      continue;
	  }
	  // First Add default number
	  if (!$value[3]) {
	    continue;
	  }
		$un = null;
		$un->id = $value[0];
		$un->user_id = $value[1];
		$un->number = $value[2];
		$un->name = $value[2];
		$un->is_default = $value[3];
		$un->is_active = $value[4];
		$uns[$un->id] = $un;
	}
	foreach ($rps_tmp as $key => $value) {
    // Add all other numbers
	  if ($value[3]) {
	    continue;
	  }
	  if ($only_active && !$value[4]) {
      continue;
	  }
		$un = null;
		$un->id = $value[0];
		$un->user_id = $value[1];
		$un->number = $value[2];
		$un->name = $value[2];
		$un->is_default = $value[3];
		$un->is_active = $value[4];
		$uns[$un->id] = $un;
	}
	return $uns;
}

function c2r_offers_get($user_id, $status = null, $past = 1, $company_id = null) {
  	$data = '{"method": "offers_get", "params": ['.$user_id.', '.$past.'], "id": 0}';
  	$os_tmp = post($data);
	$os = array();
	foreach ($os_tmp as $key => $value) {
		$o = null;
		$o->id = $value[0];
		$o->user_number_id = $value[1];
		$o->user_number = c2r_get_user_number($o->user_number_id);
		$o->route_id = $value[2];
		$o->route = c2r_routes_get($o->route_id);
		
		$location = c2r_locations_get($o->route->lid);	
		$timezone = $location->timezone;
		
		$o->start_time = convertDatetime($value[3], $timezone);
		$o->request_time = $value[4];
		$o->status = $value[5];
		$o->reverse = $value[6];
		
		$user_id = c2r_get_user_id_by_user_number_id($o->user_number_id);
		$theuser = c2r_user_get($user_id);
		$o->user = $theuser;
		
		if ($company_id && $o->user->company_id != $company_id) {
		  continue;
		}
		
		if (!$status || $status==$o->status) {
			$os[$o->id] = $o;
		}
	}
	return $os;
}

/**
  Returns a single Offer
*/
function c2r_offer_get($id) {
  $data = '{"method": "offer_get", "params": ['.$id.'], "id": 0}';
  $value = post($data);

  $o = null;
	$o->id = $value[0];
	$o->user_number_id = $value[1];
	$o->user_number = c2r_get_user_number($o->user_number_id);
	$o->route_id = $value[2];
	$o->route = c2r_routes_get($o->route_id);
	
	$location = c2r_locations_get($o->route->lid);	
	$timezone = $location->timezone;

	$o->start_time = convertDatetime($value[3], $timezone);
	$o->request_time = $value[4];
	$o->status = $value[5];
	$o->reverse = $value[6];
	
	return $o;
}

/**
  Returns a single Request
*/
function c2r_request_get($id) {
  $data = '{"method": "request_get", "params": ['.$id.'], "id": 0}';
  $value = post($data);
  $r = null;
	$r->id = $value[0];
	$r->user_number_id = $value[1];
	$r->user_number = c2r_get_user_number($r->user_number_id);		
	$r->start_point_id = $value[2];
	$r->start_point = c2r_pickuppoints_get($r->start_point_id);
			
	$location_id =	$r->start_point->lid;
	$location = c2r_locations_get($location_id);	
	$timezone = $location->timezone;	
	
	$r->end_point_id = $value[3];
	$r->end_point = c2r_pickuppoints_get($r->end_point_id);
	
	$r->earliest_start_time = convertDatetime($value[4], $timezone);
	$r->latest_start_time = convertDatetime($value[5], $timezone);
	
	$r->request_time = $value[6];
	$r->status = $value[7];
	
  return $r;
}

function convertDatetime($datetime, $timezone) {
  $t = new DateTime($datetime);
  $t->setTimeZone(new DateTimeZone($timezone));
  return $t->format('Y-m-d H:i');;
}

function c2r_requests_get($user_id, $status = null, $past = 1, $company_id = null) {
  	$data = '{"method": "requests_get", "params": ['.$user_id.', '.$past.'], "id": 0}';
  	$rs_tmp = post($data);
	$rs = array();
	foreach ($rs_tmp as $key => $value) {
		$r = null;
		$r->id = $value[0];
		$r->user_number_id = $value[1];
		$r->user_number = c2r_get_user_number($r->user_number_id);		
		$r->start_point_id = $value[2];
		$r->start_point = c2r_pickuppoints_get($r->start_point_id);
				
		$location_id =	$r->start_point->lid;
		$location = c2r_locations_get($location_id);	
		$timezone = $location->timezone;	
	
			
		$r->end_point_id = $value[3];
		$r->end_point = c2r_pickuppoints_get($r->end_point_id);
		
		$r->earliest_start_time = convertDatetime($value[4], $timezone);
		$r->latest_start_time = convertDatetime($value[5], $timezone);
		
		$r->request_time = $value[6];
		$r->status = $value[7];
		
		$user_id = c2r_get_user_id_by_user_number_id($r->user_number_id);
		$theuser = c2r_user_get($user_id);
		$r->user = $theuser;
		
		if ($company_id && $r->user->company_id != $company_id) {
		  continue;
		}
		
		if (!$status || $status==$r->status) {
			$rs[$r->id] = $r;
		}
	}
	
	return $rs;
}

function c2r_close_single_offer($offer_id) {
  $data = '{"method": "close_single_offer", "params": ['.$offer_id.'], "id": 0}';
	return post($data);
}

function c2r_close_single_request($request_id) {
  $data = '{"method": "close_single_request", "params": ['.$request_id.'], "id": 0}';
	return post($data);
}

function c2r_companies_insert($name, $smskey, $zendeskid, $logourl, $email) {
	$data = '{"method": "companies_insert", "params": ["'.$name.'", "'.$smskey.'", "'.$zendeskid.'", "'.$logourl.'", "'.$email.'"], "id": 0}';
	return post($data);
}

function c2r_locations_insert($name, $cid, $timezone, $phone) {
	$data = '{"method": "locations_insert", "params": ["'.$name.'", '.$cid.', "'.$timezone.'", "'.$phone.'"], "id": 0}';
	return post($data);
}

function c2r_pickuppoints_insert($name, $lid, $geo, $key) {
	$data = '{"method": "pickuppoints_insert", "params": ["'.$name.'", '.$lid.', "'.$geo.'", "'.$key.'"], "id": 0}';
	return post($data);
}
	
function c2r_routes_insert($origin, $destination, $status, $key, $lid, $user_id) {
	$data = '{"method": "routes_insert", "params": ["'.$origin.'", "'.$destination.'", "'.$status.'", "'.$key.'", '.$lid.', '.$user_id.'], "id": 0}';
	return post($data);
}

function c2r_companies_delete($id) {
	$data = '{"method": "companies_delete", "params": ['.$id.'], "id": 0}';
	return post($data);
}

function c2r_locations_delete($id) {
	$data = '{"method": "locations_delete", "params": ['.$id.'], "id": 0}';
	return post($data);
}

function c2r_pickuppoints_delete($id) {
	$data = '{"method": "pickuppoints_delete", "params": ['.$id.'], "id": 0}';
	return post($data);
}

function c2r_routes_delete($id) {
	$data = '{"method": "routes_delete", "params": ['.$id.'], "id": 0}';
	return post($data);
}

function c2r_companies_update($id, $name, $smskey, $zendeskid, $logourl, $email) {
	$data = '{"method": "companies_update", "params": ['.$id.', "'.$name.'", "'.$smskey.'", "'.$zendeskid.'", "'.$logourl.'", "'.$email.'"], "id": 0}';
	return post($data);
}

function c2r_locations_update($id, $name, $cid, $timezone, $phone) {
	$data = '{"method": "locations_update", "params": ['.$id.', "'.$name.'", '.$cid.', "'.$timezone.'", "'.$phone.'"], "id": 0}';
	return post($data);
}

function c2r_pickuppoints_update($id, $name, $lid, $geo, $key) {
	$data = '{"method": "pickuppoints_update", "params": ['.$id.', "'.$name.'", '.$lid.', "'.$geo.'", "'.$key.'"], "id": 0}';
	return post($data);
}

function c2r_routes_update($id, $origin, $destination, $status, $key, $lid, $user_id) {
	$data = '{"method": "routes_update", "params": ['.$id.', "'.$origin.'", "'.$destination.'", "'.$status.'", "'.$key.'", '.$lid.', '.$user_id.'], "id": 0}';
	return post($data);
}

function c2r_routepoints_delete($rid) {
	$data = '{"method": "routepoints_delete", "params": ['.$rid.'], "id": 0}';
	return post($data);
}

function c2r_routepoints_insert($rid, $pid, $st, $pos) {
	$data = '{"method": "routepoints_insert", "params": ['.$rid.', '.$pid.', "'.$st.'", '.$pos.'], "id": 0}';
	return post($data);
}

function c2r_user_update_default_location($uid, $dlid) {
	$data = '{"method": "user_update_default_location", "params": ['.$uid.', '.$dlid.'], "id": 0}';
	return post($data);
}

function c2r_user_update_language($uid, $language) {
	$data = '{"method": "user_update_language", "params": ['.$uid.', "'.$language.'"], "id": 0}';
	return post($data);
}

function c2r_user_number_default($uid, $dnid) {
	$data = '{"method": "user_number_default", "params": ['.$uid.', '.$dnid.'], "id": 0}';
	return post($data);
}

function c2r_user_number_delete($unid) {
	$data = '{"method": "user_number_delete", "params": ['.$unid.'], "id": 0}';
	return post($data);
}

function c2r_user_number_add($uid, $number) {
	$code = substr(md5($handynr.time()), -7, -2);
	$data = '{"method": "user_number_add", "params": ['.$uid.', "'.$number.'", "'.$code.'"], "id": 0}';
	return post($data);
}

function c2r_user_number_add_admin($uid, $number) {
	$data = '{"method": "user_number_add_admin", "params": ['.$uid.', "'.$number.'"], "id": 0}';
	return post($data);
}

function c2r_user_number_activate($unid, $code) {
	$data = '{"method": "user_number_activate", "params": ['.$unid.', "'.$code.'"], "id": 0}';
	return post($data);
}

function c2r_userinfo_update($uid, $name, $email, $cid, $dlid, $gid, $is_active, $language) {
	$data = '{"method": "userinfo_update", "params": ['.$uid.', "'.$name.'", "'.$email.'", '.$cid.', '.$dlid.', '.$gid.', '.$is_active.', "'.$language.'"], "id": 0}';
	return post($data);	
}

function c2r_userinfo_insert($name, $email, $cid, $dlid, $gid, $is_active, $number) {
	$data = '{"method": "userinfo_insert", "params": ["'.$name.'", "'.$email.'", '.$cid.', '.$dlid.', '.$gid.', '.$is_active.', "'.$number.'"], "id": 0}';
	return post($data);	
}

function c2r_log_error($code, $message) {
	global $user;
	$data = '{"method": "log_error", "params": ['.$user->id.', "'.$code.'", "'.$message.'"], "id": 0}';
	return post($data);	
}

function c2r_change_password($new_password, $user_id = 0) {
	global $user;
	if (!$user_id) {
	  $user_id = $user->id;
	}
	$data = '{"method": "change_password", "params": ['.$user_id.', "'.$new_password.'"], "id": 0}';
	return post($data);		
}

function c2r_user_delete($uid) {
	$data = '{"method": "user_delete", "params": ['.$uid.'], "id": 0}';
	return post($data);	
}

function c2r_lost_password($email) {
	$data = '{"method": "lost_password", "params": ["'.$email.'", "'.OCP_BASE_URL.'"], "id": 0}';
	return post($data);	
}

function c2r_get_matches_for_request($request_id) {
  $data = '{"method": "get_matches_for_request", "params": ['.$request_id.'], "id": 0}';
  #print_r($data);
	$r = post($data);
	return $r;
}

function c2r_get_matches_for_offer($offer_id) {
  $data = '{"method": "get_matches_for_offer", "params": ['.$offer_id.'], "id": 0}';
  #print_r($data);
	$r = post($data);
	return $r;
}


/* posts to Python backend service, returns result list as array */
function post($data, $debug=false) {
  return post_getjson($data)->result;
}

/* posts to Python backend, returns response as JSON object */
function post_getjson($data) {
    return json_decode(post_getstring($data));
}

/* posts to Python backend, returns response as JSON string */
function post_getstring($data) {
  $x = PostToHost(OCP_SERVICE_HOST,"/JSON",$data);
  if ($x==NULL) {
    return '{"error": "post to host failed, python service down?", "id": 0, "result": []}';
  }
  list($header,$body) = split("\r\n\r\n",$x);
  return $body;
}

function c2r_getHtmlSelect($name, $array, $objattr, $selected=null, $extra=null) {
	$html = "<select name=\"$name\" id=\"$name\" $extra>";
	foreach ($array as $key => $value) {
		$sel = '';
		if ($selected==$key) $sel=' selected="selected"';
		$html .= "<option$sel value=\"$key\">".$value->$objattr."</option>";
	}
	$html .= "</select>";
	return $html;
}

function c2r_getHtmlSelectStatus($name, $selected=null)
{
	$array = c2r_getStatusArray();
	
	return c2r_getHtmlSelect($name, $array, 'name', $selected);
}

function c2r_getStatusArray() {
  $stat_enabled->name = 'enabled';
	$stat_disabled->name = 'disabled';
	$array = array('enabled' => $stat_enabled, 'disabled' => $stat_disabled);
	return $array;
}

function c2r_getHmtlSelectPoints($name, $lid, $selected=null, $extra = '') {
	$liste = c2r_pickuppoints_get(0, $lid);
	return c2r_getHtmlSelect($name, $liste, 'name', $selected, $extra);
} 

function c2r_getHmtlSelectUserNumbers($user_id, $selected=null) {
	$liste = c2r_user_number_get($user_id);
	return c2r_getHtmlSelect('user_number_id', $liste, 'number', $selected);
}

function c2r_getHtmlSelectTimezones($selected=null) {
	$array = c2r_getTimeZonesArray();
	return c2r_getHtmlSelect('timezone', $array, 'name', $selected);
}

function c2r_getTimeZonesArray() {
  $tzs = c2r_timezones_get();
  $array = array();
	foreach ($tzs as $key => $value) {
		$obj = null;
		$obj->name = $value;
		$array[$value] = $obj;
	}
	return $array;
}

function c2r_get_user_id_by_user_number_id($user_number_id) {
  //get_user_id_by_user_number_id
  $data = '{"method": "get_user_id_by_user_number_id", "params": ['.$user_number_id.'], "id": 0}';
	return post($data);
}

/*
function c2r_getHtmlSelectKeys($uid=null, $lid=null, $selected=null)
{
	
}
*/

function PostToHost($host, $path, $data_to_send) {
  $host_parts = explode(":", $host);
  $res = NULL;
  if ($fp = fsockopen($host_parts[0], $host_parts[1], $errno, $errstr, 10)) {
    stream_set_timeout($fp, 5);
    fputs($fp, "POST $path HTTP/1.1\r\n");
    fputs($fp, "Content-type: application/json-rpc\r\n");
    fputs($fp, "Content-length: ". strlen($data_to_send) ."\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $data_to_send);
    while(!feof($fp)) {
      $res .= fgets($fp, 128);
    }
    fclose($fp);
  }
  return $res;
}

function messages($msgs, $errors)
{
	if (count($msgs)) {
		echo '<div class="message">';
		echo join('<br />', $msgs);
		echo '</div>';
	}
	if (count($errors)) {
		echo '<div class="warn">';
		echo join('<br />', $errors);
		echo '</div>';
	}
}

function query($sql) {
    // DB Eintrag
    global  $connection_string;
    $dbconn = pg_connect(OCP_CONNECTION_STRING) or die('Could not connect: ' . pg_last_error());
    $result = pg_query($sql) or die('Query failed: '.$sql . pg_last_error());
    return $result;
}

function str2hex($str)
{
    $hex = "";
    $l = strlen($str);
    
    for($i = 0; $i < $l; $i++)
    {       
        $hex .= str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT);
    }
    
    return strtoupper($hex);
}

function c2r_get_EMAILSUFFIXes($company_id = 0) {
  $sql = "select es.id, es.location_id, es.suffix, l.\"Name\" from emailsuffix es, location l where ".($company_id ? 'l.company_id = '.$company_id.' and ' : '')."l.id = es.location_id";
  $result = query($sql);
  $all = array();
  while ($row = pg_fetch_row($result)) {
    $es = new stdClass;
    $es->id = $row[0];
    $es->location_id = $row[1];
    $es->suffix = $row[2];
    $es->location_name = $row[3];
    $all[] = $es;
  }
  return $all;
}

function c2r_update_emailsuffix($id, $suffix, $location_id) {
  $sql = "update emailsuffix set suffix='$suffix', location_id = $location_id where id = $id";
  query($sql);
}

function c2r_insert_emailsuffix($suffix, $location_id) {
  $sql = "insert into emailsuffix (suffix, location_id) values ('$suffix', $location_id)";
  query($sql);
}

function c2r_delete_emailsuffix($id) {
  $sql = "delete from emailsuffix where id = $id";
  query($sql);
}

function c2r_get_emailsuffix($id) {
  $sql = "select es.id, es.location_id, es.suffix, l.\"Name\" from emailsuffix es, location l where es.id=$id and l.id = es.location_id";
  $result = query($sql);
  if ($row = pg_fetch_row($result)) {
    $es = new stdClass;
    $es->id = $row[0];
    $es->location_id = $row[1];
    $es->suffix = $row[2];
    $es->location_name = $row[3];
    return $es;
  }
  return null;
}

function getDefaultLocationAndCompanyFromEmail($email) {
  list(, $suffix) = explode('@', $email, 2);
  
  $sql = "select es.location_id, l.company_id from emailsuffix es, location l where es.suffix = '$suffix' and l.id = es.location_id";
  $result = query($sql);
  if ($row = pg_fetch_row($result)) {
     $es->location_id = $row[0];
     $es->company_id = $row[1];
     return array($es->location_id, $es->company_id);
   }
   return array(OCP_DEFAULT_LOCATION_ID, OCP_DEFAULT_COMPANY_ID);
}

function registerUser($row, $pw) {
    $email = $row[3];
    list($default_location_id, $company_id) = getDefaultLocationAndCompanyFromEmail($email);
  
    $sql = "update user_registration set status=0 where id=".$row[0];
    $result = query($sql);

    $pw_secured = md5('abc123'.$pw);
    
    $lang = $_COOKIE['language'];
    
    $sql = "insert into users (id, name, is_active, email, password, company_id, default_location_id, group_id, language) values (nextval('users_id_seq'::regclass), '".$row[1]."', true, '".$row[3]."', '$pw_secured', $company_id, $default_location_id, 1, '$lang')";
    $result = query($sql);

    $sql = "SELECT Currval('users_id_seq') LIMIT 1";
    $result = query($sql);
    if ($row_id = pg_fetch_row($result)) {
      $userid = $row_id[0];
    }

    $sql = "insert into user_number (id, user_id, number, is_default, is_active) values (nextval('user_numbers_id_seq'), $userid, '".$row[2]."', TRUE, TRUE)";
    $result = query($sql);

    sendRoutes($row[2], $row[3], $pw);
    sendVCARD($row[2]);
    
    setUser($userid);
}

function sendVCARD($nr) {
    $vcard = "BEGIN:VCARD
VERSION:2.1
N:OpenCarPool
TEL;WORK;VOICE:03060989977
EMAIL:outlook.dev@opencarpool.org
END:VCARD";

    $message = str2hex($vcard);
    $url =  'https://gateway.smstrade.de?key='.OCP_SMS_KEY.'&to='.$nr;
    $url .= '&route=gold';
    $url .= '&ref=OpenCarPool';
    $url .= '&from='.urlencode('OpenCarPool');
    $url .= '&udh=06050423F423F4';
    $url .= '&messagetype=binary';
    $url .= '&message='.($message);
    #$url .= '&message=hallo';

    // Zur Zeit deaktiviert
    #$content = file_get_contents($url);
}

function sendRoutes($nr, $email, $pw) {
  $sms_key = getBestSMSKeyForEmail($email);
  list($default_location_id, $company_id) = getDefaultLocationAndCompanyFromEmail($email);
  
  $location = c2r_locations_get($default_location_id);    
  $phone = $location->phone;
  
  $sql = "select key, origin, destination from routes where status='enabled' and user_id=0 and location_id=$default_location_id order by id";

  $result = query($sql);
  while ($row = pg_fetch_row($result)) {
    $routes[] = $row[0].': '.$row[1];
  }
  $message = t("Available routes at your location are: \n").implode("\n", $routes);
  $message2 = urlencode($message);
  $subject = t("Open CarPool: You have been registered successfully");
  $msg =  t("Thank you for registering at Open CarPool. You can now protect your environment, reduce costs, share ideas and extend your network via carpooling at your company.\n\n");
  $msg .= t("To offer or request a lift, log in via your computer or smart phone at https://web.opencarpool.org using with your email address $email and your temporary password '$pw'. You can also offer or request lifts via any cell phone at $phone or enter recurring lifts in your Outlook calendar by inviting outlook@opencarpool.org.\n\n");

  $msg .= $message;

  $msg .= t("\n\n For a detailed description please visit http://www.opencarpool.org.\n");
  $msg .= t("\n For support or feedback please send a mail to support@opencarpool.org.\n");

  mail($email, $subject, $msg, "From:Open CarPool <support@opencarpool.org>", "-f support@opencarpool.org");
  $url =  'https://gateway.smstrade.de?key='.$sms_key.'&to='.$nr;
  $url .= '&route=gold';
  $url .= '&ref=OpenCarPool';
  $url .= '&from='.urlencode('OpenCarPool');
  $url .= '&concat_sms=1';
  $url .= '&message='.($message2);
    
  $content = file_get_contents($url);
}

function checkCode($code, $smscode=null) {
    $sql = "select id, name, cell_phone_nr, email from user_registration where status = 1 and code_email = '$code'";
    if ($smscode) {
        $sql .= " and code_cell_phone = '$smscode'";
    }
    $result = query($sql);
    if ($row = pg_fetch_row($result)) {
        if ($smscode) {
            registerUser($row, $smscode);
        }
        return TRUE;
    }
    return FALSE;
}

function checkSmsCode($code, $smscode) {
    return checkCode($code, $smscode);
}

function checkEmailCode($code) {
    return checkCode($code);
}
 
function getCodes($email, $handynr, $name) {
    $email_code = substr(md5($email.time()), -10, -2);
    $cell_code = substr(md5($handynr.time()), -7, -2);

    $name = pg_escape_string($name);
    $handynr = pg_escape_string($handynr);
    $email = pg_escape_string($email);

    $sql =  "insert into user_registration (name, cell_phone_nr, email, code_cell_phone, code_email, created, status)";
    $sql .= " values ('$name', '$handynr', '$email', '$cell_code', '$email_code', now(), 1)";
    $result = query($sql);

    if ($result) {
        sendCodeMail($email, $email_code, $name);
        sendCodeHandy($handynr, $cell_code, $email);
        return array('email' => $email_code, 'handynr' => $cell_code);
    }

    return FALSE;
}

function getLink($code) {
    return OCP_BASE_URL.'?code='.$code;
}

function sendCodeMail($email, $code, $name) {
    $subject = 'Open CarPool Confirmation Link for '.$name;
    $msg = t('This is the Open CarPool registration confirmation mail for ').$name.'. ';
    $msg .=  t("Please enter the confirmation code we sent to your mobile phone into this form: ");
    $msg .= OCP_BASE_URL."register.php?code=$code";
    $msg .= t(". This is to assure that both your email address and phone number are valid.\n\n");
    mail($email, $subject, $msg, "From: Open CarPool <support@opencarpool.org>",
     "-f support@opencarpool.org");
}

function getBestSMSKeyForEmail($email) {
  list($default_location_id, $company_id) = getDefaultLocationAndCompanyFromEmail($email);
  $company = c2r_companies_get($company_id);    
  $sms_key = $company->smskey;
  if (!$sms_key) {
    $sms_key = OCP_SMS_FALLBACK;
  }
  return $sms_key;
}

function SendCodeHandy($nr, $code, $email) {
  
    $sms_key = getBestSMSKeyForEmail($email);
  
    $url =  'https://gateway.smstrade.de?key='.$sms_key.'&to='.$nr;
    $url .= '&route=gold&ref=OpenCarPool&concat_sms=1&cost=1&message_id=1';
    $url .= '&from=OpenCarPool&charset=UTF-8&message='.urlencode('The registration code is:'.$code);
 
    $content = file_get_contents($url);
    // Check if content starts with 100
}

//works:
function SendCodeHandy_working_copy($nr, $code) {
    $url =  'https://gateway.smstrade.de?key='.OCP_SMS_KEY.'&to='.$nr;
    $url .= '&route=gold&ref=OpenCarPool&concat_sms=1&cost=1&message_id=1';
    $url .= '&from=OpenCarPool&charset=UTF-8&message=Call2Ride-Code:'.$code;
    
    $content = file_get_contents($url);
    // Check if content starts with 100
}

/* Translation */
function translate($text) {
  global $user;
  if ($user) {
    $lng = $user->ui->language;
  } else {
    $lng = OCP_LANGUAGE;
  }
  if (!$lng) {
    $lng = 'en';
  }
  if ($lng == 'en') {
    return $text;
  }
  $textsql = pg_escape_string($text);
  $sql =  "select translation from translation where key='$textsql' and lang='".$lng."'";
  $result = query($sql);
  if ($row = pg_fetch_row($result)) {
    if ($row[0]) {
      return $row[0];
    } 
  } else {
    $sql = "insert into translation (key, lang) values ('$textsql', '".$lng."')";
    query($sql);
  }
  return $text;
}

function t($t) {
  return translate($t);
}

function getAllTranslations($lang = null) {
  $sql = 'select * from translation';
  if ($lang) {
    $sql .= " where lang = '$lang'";
  }
  $result = query($sql);
  $t = array();
  while($row = pg_fetch_row($result)) {
    $r = new stdClass;
    $r->id = $row[0];
    $lang = $row[1];
    $r->key = $row[2];
    $r->translation = $row[3];
    $t[$lang][$r->id] = $r;
  }
  return $t;
}

function updateTranslation($id, $text) {
  $sql = "update translation set translation = '$text' where id = $id";
  query($sql);
}

function c2r_getLanguages() {
  global $ocp_languages;
  $r = array();
  foreach ($ocp_languages as $key => $value) {
    $o = new stdClass;
    $o->name = $value;
    $r[$key] = $o;
  }
  return $r;
}

function c2r_accept_offer($driverId, $offerId, $phoneNr) {
    $send_sms = 1;
    $data = '{"method": "accept_offer", "params": ['.$driverId.','.$offerId.',"'.$phoneNr.'", '.$send_sms.'], "id": 0}';
	return post($data);
}


function c2r_create_notification($uid, $message) {
  $data = '{"method": "create_notification", "params": ['.$uid.',"'.$message.'"], "id": 0}';
  return post($data);
}

function c2r_mark_notification_read($id) {
  $data = '{"method": "mark_notification_read", "params": ['.$id.'], "id": 0}';
  return post($data);
}


function c2r_get_notification($uid) {
  $data = '{"method": "get_notification", "params": ['.$uid.'], "id": 0}';

  return post($data);
}

function sort_requests($a, $b) {
  return ($a->earliest_start_time > $b->earliest_start_time) ? 1 : -1;
}

function sort_offers($a, $b) {
  return ($a->start_time > $b->start_time) ? 1 : -1;
}