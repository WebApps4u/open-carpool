<?php

 // somebody offers --> check requests --> if matching --> send sms to requester

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

c2r_get_matches_for_request($request_id)