<?php

$t_title = <<<EOT
{t t='Open CarPool: Registration'}
EOT;

$t_heading = <<<EOT
{t t='Open CarPool Registration'}
EOT;

$t_intro = t(
'<div>After submitting this form you will receive
<ul>
	<li>an email with a link to a confirmation page</li>
	<li>a text message with a confirmation code to be entered there</li>
</ul>
When you offer a lift, your name, email address and phone number will be sent to matching colleagues requesting that lift in order to be able to contact you to arrange a pickup. Please do not proceed if you do not agree to this.
<div />'
);

$t_disclaimer = t('
<div>When you offer a lift, your name and phone number will be will be sent to matching colleagues requesting that lift so that they can call you. Your email address will only be used for account confirmation and account handling and will not be shared.<div />
');

$t_name = t('
Name
');

$t_email = t('
Corporate Email Address
');

$t_phone = t('
Mobile Phone Number                                                                                                
  (Format: <a href=\'http://en.wikipedia.org/wiki/List_of_country_calling_codes\' target=\'_blank\'>+1</a>234567890)
');

$t_format = t('
Format
');

$t_submit = t('
Submit
');

$t_error = t('
There was an error. Please correct your form entries.<br />
For support or feedback please send a mail to <a href="mailto:service@opencarpool.org">service@opencarpool.org</a>.
');

$t_error_name = t('
Please enter your full name as it should be shown to your colleagues. Please do not use a nickname unless it is well known within the company.
');

$t_error_mail = t('
Please your corporate email address. We need your email address to send you a confirmation link and to proof that you are an eBay employee. We will only accept eBay corporate email addresses. It will only be used for account confirmation and account handling and will not be shared.
');

$t_error_mail2 = t('
The email address is already in use. Please use another email address to register or use the password reset function for the existing email address.
');

$t_error_phone = t('
Please enter your phone mobile number.  The format must start with the "+" sign, followed by the country code, the area code and your local phone number without any blanks or special characters: e.g. +1234567890<br />
We need your phone number in order to send you the confirmation code and identify you as a registered user when calling the system.
');

$t_error_phone2 = t('
This phone number is already in use. Please log in into your existing account or contact us for support at <a href="mailto:service@opencarpool.org">service@opencarpool.org</a>
');

$t_entercode = t('
<div>Please enter the confirmation code you received via a SMS text message on your mobile phone.</div>
');

$t_entercode2 = t('
<div>This is to ensure that this phone number matches with your corporate email address and both the email address and the phone number are valid.</div>
<div>In case you didn\'t receive a text message after 10 minutes, please re-register and check for typos in the phone number.</div>
');

$t_confirmcode = t('
Text Message Confirmation Code
');

$t_error_code = t('
<div>Sorry. This code is not valid. Please check for typos and enter a valid code.</div>
<div>For support or feedback please send a mail to <a href="mailto:support@opencarpool.org">support@opencarpool.org</a>.</div>
');

$t_thankyou = t('
<div>Thank you for your registration. Please
<ol>
	<li>Check your mailbox and click on the link we have sent you via email to %email%. It will open a confirmation page.</li>
	<li>Check your text messages and enter the confirmation code we have sent you into the confirmation page form.</li>
</ol>
</div>
<div>This is to assure that both your email address and phone number is valid.<br />
If you did not receive the mail and text message after five minutes, please check your junk mail folder or contact us via the Service tab.</div>
');

$t_finish = t('
Thank you for your confirmation. You have been registered successfully at Open CarPool. <br />
You will now receive an email with available routes. We will also send you the routes via a text message to your mobile phone so that you have it always with you.<br />
Good speed!
');

?>