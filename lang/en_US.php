<?php
#A
$lang['add_mobile'] = 'Add Mobile';
$lang['addedit_mobile'] = 'Add/Edit a Mobile Phone Record';
$lang['ask_delete_mobile'] = 'Are you sure you want to delete this mobile phone number from the database?';

#B


#C
$lang['cancel'] = 'Cancel';
$lang['custom'] = 'Custom';

#D
$lang['default_templates'] = 'Default Templates';
$lang['dflt_enternumber_template'] = 'System Default &quot;Enter Your Number&quot; Template';
$lang['dflt_entertext_template'] = 'System Default &quot;Enter Your Message Text&quot; Template';

#E
$lang['enter_mobile_number'] = 'Enter Mobile Phone Number';
$lang['enter_number_templates'] = '&quot;Enter Your Number&quot; Templates';
$lang['enter_text_templates'] = '&quot;Send A Message&quot; Templates';
$lang['error_db_op_failed'] = 'Database operation failed, please contact system administrator';
$lang['error_invalid_info'] = 'One or more values specified are invalid';
$lang['error_invalid_number'] = 'The mobile phone number you entered is invalid';
$lang['error_invalid_text'] = 'The text you entered contains invalid characters';
$lang['error_name_exists'] = 'An item by that name already exists';
$lang['error_nogatewaysfound'] = 'Could not find any SMS gateways';
$lang['error_notfound'] = 'The requested item could not be found';

#F
$lang['friendlyname'] = 'Calguys SMS Utility';
$lang['from'] = 'From';

#G
$lang['googlevoice_sms_gateway'] = 'Google Voice SMS Gateway';

#H
$lang['help_action'] = <<<EOT
This parameter is used to decide the behavour of the module.  Valid values for this parameter are:
<ul>
<li>enternumber <em>(default)</em> - Display a link to a form allowing the user to send pre-defined text to a user-defined mobile phone number.</li>
<li>entertext - Display a link to a form allowing the user to send user-defined text to a pre-defined mobile phone number.</li>
</ul>
EOT;
$lang['help_smstext'] = 'Applicable only to the enternumber action, this parameter defines the text that will be sent in the sms message.   The text is never displayed in the HTML output of the page for security reasons.';
$lang['help_linktext'] = 'This parameter allows overriding the text that is displayed in the link.  This parameter has no effect when the urlonly parameter is used.';
$lang['help_urlonly'] = 'This parameter indicates that instead of a full link only the URL should be echoed, allowing you to build your own link.';
$lang['help_inline'] = 'This parameter indicates that the form should replace the link (instead of the default content area of the page).  It is not useful with the destpage parameter';
$lang['help_destpage'] = 'This parameter indicates that the resulting form should be displayed on a different CMSMS content page (specified by page id or alias)';
$lang['help_smsnum'] = 'Applicable only to the entertext action, this parameter defines the numeric ID of a pre-defined mobile phone number';
$lang['help_enternumbertemplate'] = 'Applicable only to the enternumber action, this parameter allows specifying a non default enternumber template to create the form with';
$lang['help_entertexttemplate'] = 'Applicable only to the entertext action, this parameter allows specifying a non default entertext template to create the form with';
$lang['help'] = <<<EOT
<h3>What Does This Do?</h3>
<p>This module allows website visitors to either send a pre-determined (and encrypted) text message to any text capable phone, or to allow sending a user specified text message to a pre-determined (and hidden) mobile phone number.  It is useful for such things as sending addresses from a directory to a persons mobile phone number... or for messaging a website administrator with urgent information.</p>
<h3>Security</h3>
<p>Attempts have been made to ensure a reasonable level of security at all times.  This is accomplished in a variety of ways:</p>
<ul>
<li>1, All text messages are checked for length limits, and valid characters before sending.</li>
<li>2. All text messages are recorded in the database, including the IP address of the sender.</li>
<li>3. Before any text message is sent, a check is made to ensure that the user has not exceeded the maximum amount of messages sent for that IP address.</li>
<li>4. When sending to a pre-defined mobile phone the SMS number is hidden from the website visitor at all times to prevent spamming.</li>
<li>5. When sending a pre-defined message the text of the message is stored in the database and the visitor is given a unique key to the text of the message, preventing alteration of the text itself.</li>
</ul>
<h3>SMS Gateways</h3>
<p>This module depends on selecting a supported SMS gateway and filling in the required authentication information.   You will need to sign up to at least one of the supported gateways.  This may involve paying money to a thhird party.</p>
<p>Different SMS Gateways will have different requirements and limitations on the amount, size, source, and destination messages that can be sent... it is your responsibility to understand these limitations.</p>
<p>Currently, these gateways are Implemented:</p>
<ul>
  <li><a href="http://interlinked.mobi"><strong>Interlinked.mobi</strong></a> - A UK based SMS marketing provider.</li>
<li><a href="http://voice.google.com"><strong>Google Voice</strong></a> - An experimental (but working at this time) utility to allow sending SMS messages through Google Voice.</li>
</ul>
<h3>How do I use it</h3>
<p>The first thing you should do is to go into the modules admin panel under &quot;Extensions &gt;&gt; Calguys SMS Utility&quot;.  There you will be able to choose one of the available gateways and to enter the authentication information for that gateway.  At this time you can also define the SMS sending limits to reduce spam.</p>
<p>Secondly you should perform at least one SMS Test (there is a tab in the module admin panel to allow this) to ensure that SMS messages are being sent to your mobile phone.</p>
<p>Thirdly you need to place at least one tag into a page, or page tempalte in the CMSMS Admin console.  There are two primary ways in which to use this module:</p>
<ul>
   <li>1. Send predefined text to a user defined mobile number <code>{CGSMS action='enternumber' text='the quick brown fox'}</code>
    <p>This will generate a link that when clicked will display a form to the user to allow them to enter a mobile phone number.</p>
  </li>
  <li>2. Send user defined text to a predefined mobile number <code>{CGSMS action='entertext' smsnum=5}</code>
  <p>This will generate a link that when clicked will display a form to allow the user to enter a 160 character SMS Message that is then sent to a predetermined mobile number witht he id=5.</p>
  </li>
</ul>
<h3>API</h3>
<p>This module contains a complete API to allow sending messages from other modules or from UDTs. A brief example of how to do this is below... for a complete illustration of the API look at the lib/class.cgsms_sender_base.php file.</p>
<pre style="margin-left: 5em;"><code>
\$gateway = cgsms_utils::get_gateway();
\$gateway->set_msg('hello world');
\$gateway->set_num('12225551212');
\$gateway->send();
</code></pre>
<h3>Requirements:</h3>
<p>The requirements are numerous, please use caution:</p>
<ul>
<li>CMS Made Simple 1.6.6 or greater</li>
<li>PHP Version 5.2+ (5.2.11 or better is recommended)</li>
<li>A subscription or access to at least one suported gateway.</li>
<li>Your host must allow outgoing HTTP connections</li>
</ul>
<h3>Support</h3>
<p>This module does not include commercial support. However, there are a number of resources available to help you with it:</p>
<ul>
<li>For the latest version of this module, FAQs, or to file a Bug Report or buy commercial support, please visit calguy\'s
module homepage at <a href="http://calguy1000.com">calguy1000.com</a>.</li>
<li>Additional discussion of this module may also be found in the <a href="http://forum.cmsmadesimple.org">CMS Made Simple Forums</a>.</li>
<li>The author, calguy1000, can often be found in the <a href="irc://irc.freenode.net/#cms">CMS IRC Channel</a>.</li>
<li>Lastly, you may have some success emailing the author directly.</li>  
</ul>
<h3>Copyright and License</h3>
<p>Copyright &copy; 20010, Robert Campbell <a href="mailto:calguy1000@cmsmadesimple.org">&lt;calguy1000@cmsmadesimple.org&gt;</a>. All Rights Are Reserved.</p>
<p>This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.</p>
<p>However, as a special exception to the GPL, this software is distributed
as an addon module to CMS Made Simple.  You may not use this software
in any Non GPL version of CMS Made simple, or in any version of CMS
Made simple that does not indicate clearly and obviously in its admin 
section that the site was built with CMS Made simple.</p>
<p>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Or read it <a href="http://www.gnu.org/licenses/licenses.html#GPL">online</a></p>
TODO
EOT;

#I
$lang['id'] = 'Id';
$lang['info_enternumber_templates'] = 'This form allows you to define and edit a template that allows website visitors to enter their mobile phone number to receive a predefined text message';
$lang['info_entertext_templates'] = 'This form allows you to define and edit a template that allows website visitors to enter a message that will be sent to a predefined mobile phone number';
$lang['info_smstest'] = 'This function will send a hardcoded message to the specified mobile phone number for testing purposes.  This functionality can be used to ensure that your settings for your selected SMS gateway are correct.  Please be aware that depending upon the gateway, it may take several minutes before your phone receives the message.';
$lang['info_sysdflt_enternumber_template'] = 'This template will be used when you create a new enternumber template.  Altering this template will have no immediate effect on any display items on your website';
$lang['info_sysdflt_entertext_template'] = 'This template will be used when you create a new entertext template.  Altering this template will have no immediate effect on any display items on your website';
$lang['interlinked_sms_gateway'] = 'Interlinked SMS Gateway';

#J


#K


#L
$lang['login'] = 'Login';

#M
$lang['mobile_number'] = 'Mobile Number';
$lang['mobile_numbers'] = 'Mobile Numbers';
$lang['module_description'] = 'A module to allow sending of SMS messages from within a CMS Made Simple Website';
$lang['msg_rec_deleted'] = 'Mobile Number Deleted';

#N
$lang['name'] = 'Name';
$lang['none'] = 'None';
$lang['number'] = 'Number';

#O


#P
$lang['password'] = 'Password';
$lang['postinstall'] = 'Module successfully installed, now please ensure that it is configured properly for use';
$lang['postuninstall'] = 'Module successfully removed';
$lang['prompt_sms_daily_limit'] = 'Number of Messages that can be sent by one IP address per day';
$lang['prompt_sms_hourly_limit'] = 'Number of Messages that can be sent by one IP address per hour';

#Q


#R
$lang['reporting_url'] = 'URL to which the sms gateway can send delivery reports';

#S
$lang['security_tab_lbl'] = 'Security / Anti-Spam';
$lang['selected_gateway'] = 'Selected Gateway';
$lang['send'] = 'Send';
$lang['send_me_message'] = 'Send me an SMS';
$lang['send_to_mobile'] = 'Send to Mobile';
$lang['settings'] = 'Settings';
$lang['sms_message_sent'] = 'Your message has been sent... Actual delivery time may vary';
$lang['sms_sent'] = 'SMS Message "%s" sent to %s from IP:%s SMSID=%s';
$lang['sms_delivery_ok'] = 'SMS Message ID:%s delivered to %s IP:%s';
$lang['sms_delivery_billing'] = 'SMS Message ID:%s billing error at %s. IP:%s';
$lang['sms_delivery_invalid'] = 'SMS Message ID:%s failed (barred or invalid number: %s) IP:%s';
$lang['sms_delivery_other'] = 'SMS Message ID:%s other error (filtered or misc error to %s) IP: %s';
$lang['sms_delivery_pending'] = 'SMS Message ID:%s pending to %s IP:%s';
$lang['sms_delivery_unknown'] = 'SMS Message ID:%s unknown error at %s. IP:%s';
$lang['sms_error_auth'] = 'Authentication Error attempting to send SMS Message "%s" to %s from IP:%s';
$lang['sms_error_blocked'] = 'Attempt to send SMS Message "%s" to %s from IP:%s';
$lang['sms_error_invalid_data'] = 'Data Error attempting to send SMS Message "%s" to %s from IP:%s';
$lang['sms_error_limit'] = 'Limit Error attempting to send SMS Message "%s" to %s from IP:%s';
$lang['sms_error_other'] = 'Unknown Error (%s) attempting to send SMS Message "%s" to %s from IP:%s';
$lang['submit'] = 'Submit';

#T
$lang['test'] = 'Test';
$lang['title_enternumber_templates'] = '&quot;Enter Your Number&quot; Template Edit Form';
$lang['title_entertext_templates'] = '&quot;Enter Your Message&quot; Template Edit Form';

#U
$lang['username'] = 'Username';

#V


#W


#X


#Y


#Z


?>