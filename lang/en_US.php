<?php
# Gateway-specific strings

$lang['advice_clickatell'] = '<a href="https://www.clickatell.com"><strong>Clickatell</strong></a> - wide-coverage gateway, mixed reviews.';
$lang['description_clickatell'] = 'Relatively-easy, wide-coverage gateway, mixed reviews';
$lang['apiid'] = 'API ID';
$lang['clickatell_auth'] = 'Authentication failure: %s';
$lang['clickatell_fail'] = 'Send message to %s failed';
$lang['clickatell_success'] = 'Sent message ID: %s to %s';

$lang['advice_googlevoice'] = '<a href="http://voice.google.com"><strong>Google Voice</strong></a> - experimental (but working at this time) utility to allow sending SMS messages through Google Voice.';
$lang['description_googlevoice'] = 'TODO';
$lang['email'] = 'Email address';

$lang['advice_smsbroadcast'] = '<a href="https://www.smsbroadcast.com.au"><strong>Smsbroadcast</strong></a> - Australia-only coverage, highly regarded';
$lang['description_smsbroadcast'] = 'Simple, Australia-only coverage gateway, highly-rated';
$lang['reference'] = 'Reference';

$lang['advice_twilio'] = '<a href="https://www.twilio.com/sms"><strong>Twilio</strong></a> - global SMS gateway, low cost, well regarded';
$lang['description_twilio'] = 'Low cost, global gateway, well regarded';
$lang['token'] = 'Token';

#A
$lang['account'] = 'Account';
$lang['add_mobile'] = 'Add Mobile';
$lang['add_parameter'] = 'add parameter';
$lang['addedit_mobile'] = 'Add/Edit a Mobile Phone Record';
$lang['apiname'] = 'API name';
$lang['ask_delete_mobile'] = 'Are you sure you want to delete this mobile phone number from the database?';

#B


#C
$lang['cancel'] = 'Cancel';
$lang['confirm_uninstall']='You\'re sure you want to uninstall the SMS Utility module?';
$lang['custom'] = 'Custom';

#D
$lang['default_templates'] = 'Default Templates';
$lang['delete'] = 'Delete';
$lang['delete_tip'] = 'delete selected parameter(s)';
$lang['dflt_enternumber_template'] = 'Default &quot;Enter Your Number&quot; template';
$lang['dflt_entertext_template'] = 'Default &quot;Enter Your Message Text&quot; template';

#E
$lang['enabled'] = 'Enabled';
$lang['encrypt'] = 'Encrypt';
$lang['enter_mobile_number'] = 'Enter Mobile Phone Number';
$lang['enter_number_templates'] = '&quot;Enter Your Number&quot; Templates';
$lang['enter_text_templates'] = '&quot;Send A Message&quot; Templates';
$lang['error_db_op_failed'] = 'Database operation failed, please contact the system administrator';
$lang['error_invalid_info'] = 'One or more specified values are invalid';
$lang['error_invalid_number'] = 'The mobile phone number you entered is invalid';
$lang['error_invalid_text'] = 'The text you entered contains invalid characters';
$lang['error_name_exists'] = 'An item by that name already exists';
$lang['error_nodatafound'] = 'No data are available';
$lang['error_nogatewaysfound'] = 'Could not find any SMS gateway';
$lang['error_noparentclass'] = 'Could not find the CGExtensions module';
$lang['error_notfound'] = 'Could not find the requested item';
$lang['event_desc_delivery'] = 'Sent when a message-delivery-report is received from the current gateway (if such reports are enabled)';
$lang['event_help_delivery'] = <<<EOS
Parameters:
<ol>
<li>'gateway' the gateway name</li>
<li>'status' the short-form status-descriptor recorded by the gateway object</li>
<li>'message' a long-form status message</li>
<li>'timestamp' a date-time string, formatted by strftime() as '%X %Z'</li>
</ol>
EOS;

#F
$lang['frame_title'] = '%s SMS Gateway';
$lang['friendlyname'] = 'SMS Utility';
$lang['from'] = 'From';

#G
$lang['gateways'] = 'Gateways';

#H
$lang['help_action'] = <<<EOT
This parameter is used to decide the behavour of the module. Valid values for this parameter are:
<ul>
<li>enternumber <em>(default)</em> - Display a link to a form allowing the user to send pre-defined text to a user-defined mobile phone number.</li>
<li>entertext - Display a link to a form allowing the user to send user-defined text to a pre-defined mobile phone number.</li>
</ul>
EOT;
$lang['help_destpage'] = 'This parameter indicates that the resulting form should be displayed on a different CMSMS content page (specified by page id or alias)';
$lang['help_dnd'] = 'You can change the order by dragging row(s)';
$lang['help_enternumbertemplate'] = 'Applicable only to the enternumber action, this parameter allows specifying a non default enternumber template to create the form with';
$lang['help_entertexttemplate'] = 'Applicable only to the entertext action, this parameter allows specifying a non default entertext template to create the form with';
$lang['help_inline'] = 'This parameter indicates that the form should replace the link (instead of the default content area of the page).  It is not useful with the destpage parameter';
$lang['help_linktext'] = 'This parameter allows overriding the text that is displayed in the link.  This parameter has no effect when the urlonly parameter is used.';
$lang['help_smsnum'] = 'Applicable only to the entertext action, this parameter defines the numeric ID of a pre-defined mobile phone number';
$lang['help_smstext'] = 'Applicable only to the enternumber action, this parameter defines the text that will be sent in the sms message. The text is never displayed in the HTML output of the page for security reasons.';
$lang['help_sure'] = 'Be <strong>very sure</strong> about what you\'re doing, before modifying anything except title(s) and/or value(s)!';
$lang['help_urlcheck'] = 'Refer to the <a href="%s">%s</a> for details';
$lang['help_urlonly'] = 'This parameter indicates that instead of a full link only the URL should be echoed, allowing you to build your own link.';
$lang['help'] = <<<EOS
<h3>What does this do?</h3>
<p>This module allows website visitors to either send a pre-determined (and encrypted) text
 message to any text capable phone, or to allow sending a user specified text message to a pre-determined
 (and hidden) mobile phone number.  It is useful for such things as sending addresses from a directory to a mobile phone number...
 or for messaging a website administrator with urgent information.</p>
<h3>How do I use it?</h3>
<p>The first thing to do is grant relevant permissions to users. Three permissions are available:</p>
<ul>
<li>Modify module settings other than templates</li>
<li>Modify module templates</li>
<li>Administration, which includes the above two plus more-extensive settings manipulation</li>
</ul>
<p>Then, via the module's admin panel (menu &quot;Extensions &gt;&gt; SMS Utility&quot;), record information
about how the module is to work.
Choose one of the available gateways and enter the corresponding interface parameters.
Choose values for module settings such as the SMS sending-limits (to reduce spam).</p>
<p>Then perform at least one test (there\'s a tab in the module admin panel to allow this) to ensure that SMS messages are being sent to your mobile phone.</p>
<br />
<p>There are two primary ways to use this module in the website front end:</p>
<ol>
 <li>To send predefined text to a user-specified phone number, put a tag like
 <code>{SMSG action='enternumber' smstext='the quick brown fox'}</code> into a page or template.
 That creates a link that when clicked will display a form for the user to enter a mobile phone number.</p>
 </li>
 <li>To send user-specified text to a predefined phone number, put a tag like
 <code>{SMSG action='entertext' smsnum=5}</code> into a page or template.
 That creates a link that when clicked will display a form for the user to enter a 160-character message
 that is then sent to a predetermined mobile number (in the example, the one with id=5)
</li>
</ol>
<h3>SMS Gateways</h3>
<p>This module depends on selecting a supported SMS gateway and recording the corresponding authentication and other parameters. You will need to sign up to at least one of the supported gateways.  This will typically involve paying money to the service provider.</p>
<p>SMS gateways will have their own requirements and limitations on the amount, size, source, and destination messages that can be sent. It is your responsibility to understand these limitations.</p>
<p>Currently, these gateways are implemented:</p>
<ul>
<li>{$lang['advice_clickatell']}</li>
<li>{$lang['advice_googlevoice']}</li>
<li>{$lang['advice_smsbroadcast']}</li>
<li>{$lang['advice_twilio']}</li>
</ul>
<h3>Extra gateways</h3>
<p>A PHP-class must be created for each gateway. Refer to the README document in the module folder .../lib/gateways.</p>
<h3>API</h3>
<p>This module contains a rich API for sending SMS messages from other modules or from UDTs.
The API for each gateway is set out in file .../lib/class.sms_gateway_base.php. It comprises</p>
<ul>
<li>get_alias()</li>
<li>get_description()</li>
<li>get_name()</li>
<li>get_status()</li>
<li>get_statusmsg()</li>
<li>multi_number_separator()</li>
<li>require_country_prefix()</li>
<li>require_plus_prefix()</li>
<li>reset()</li>
<li>send()</li>
<li>set_from(\$from)</li>
<li>set_msg(\$msg)</li>
<li>set_num(\$num)</li>
<li>support_custom_sender()</li>
<li>support_mms()</li>
</ul>
<p>A brief example of how to use it is:
<pre style="margin-left: 2em;"><code>
\$gateway = smsg_utils::get_gateway();
\$gateway->set_msg('hello world');
\$gateway->set_num('12225551212');
\$gateway->send();
</code></pre></p>
<h3>Security</h3>
<p>Attempts have been made to ensure a reasonable level of security at all times.
This is accomplished in a variety of ways:</p>
<ol>
<li>All text messages are checked for length limits and valid characters, before sending.</li>
<li>All text messages are recorded in the database, including the IP address of the sender.</li>
<li>Before any text message is sent, there\'s a check that the allowed maximum (daily, hourly) counts of messages from that IP address have not been exceeded.</li>
<li>When sending to a pre-defined phone, the destination number is hidden from the initiator, to prevent spamming.</li>
<li>When sending a message, the message text is stored in the database and cannot be altered.</li>
</ol>
<h3>Requirements:</h3>
<ul>
<li>CMS Made Simple 1.8 or greater</li>
<li>PHP Version 5.2+ (5.2.11 or better is recommended)</li>
<li>A subscription or access to at least one suported gateway</li>
<li>The website host must allow outgoing HTTP connections</li>
</ul>
<h3>Support</h3>
<p>This module is provided as-is. Please read the text of the license for the full disclaimer.</p>
<p>For help:</p>
<ul>
<li>discussion may be found in the <a href="http://forum.cmsmadesimple.org">CMS Made Simple Forums</a>; or</li>
<li>you may have some success emailing the author directly.</li>
</ul>
<p>For the latest version of the module, or to report a bug, visit the module's <a href="http://dev.cmsmadesimple.org/projects/smsg">forge-page</a>.</p>
<h3>Copyright and License</h3>
<p>Portions copyright &copy; 2015 Tom Phane &lt;tpgww@onepost.net&gt;.<br />
Portions copyright &copy; 2010 Robert Campbell &lt;calguy1000@cmsmadesimple.org&gt;.<br />
All rights reserved.</p>
<p>This module is free software. It may be redistributed and/or modified
under the terms of the GNU Affero General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.</p>
<p>This module is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
<a href="http://www.gnu.org/licenses/licenses.html#AGPL">GNU Affero General Public License</a> for more details.</p>
EOS;
$lang['helptitle'] = 'Help';

#I
$lang['id'] = 'Id';
$lang['info_enternumber_templates'] = 'This template allows website visitors to enter a mobile phone number to receive a predefined text message';
$lang['info_entertext_templates'] = 'This template allows website visitors to enter a message that will be sent to a predefined mobile phone number';
$lang['info_smstest'] = <<<EOS
To test whether the settings for the selected SMS gateway are correct, you can send a message to the specified mobile phone number.
Please be aware that it may take several minutes before the phone receives the message.
EOS;
$lang['info_sysdflt_enternumber_template'] = 'This template will be used when you create a new enternumber template.  Altering this template will have no immediate effect on any display items on your website';
$lang['info_sysdflt_entertext_template'] = 'This template will be used when you create a new entertext template.  Altering this template will have no immediate effect on any display items on your website';

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
$lang['perm_admin'] = 'Administer SMS Gateways';
$lang['perm_modify'] = 'Modify SMS Gateway Settings';
$lang['perm_templates'] = 'Modify SMS Gateway Templates';
$lang['postinstall'] = $lang['friendlyname'].' module successfully installed, now please ensure that it is configured properly for use, and apply related permissions';
$lang['postuninstall'] = $lang['friendlyname']. ' module successfully removed';
$lang['prompt_daily_limit'] = 'Number of messages that can be sent by one IP address per day';
$lang['prompt_hourly_limit'] = 'Number of messages that can be sent by one IP address per hour';
$lang['prompt_log_delivers'] = 'Record in the admin log information about received gateway-delivery-reports';
$lang['prompt_log_sends'] = 'Keep local records of sent messages';
$lang['prompt_log_retain_days'] = 'Number of days that local records (if enabled) will be retained';
$lang['prompt_master_password'] = 'Master Password';

#Q


#R
$lang['reporting_url'] = 'URL to which the SMS gateway can send delivery reports';

#S
$lang'[sample'] = 'Sample';
$lang['security_tab_lbl'] = 'Security / Anti-Spam';
$lang['select'] = 'Select';
$lang['selected_gateway'] = 'Selected Gateway';
$lang['send'] = 'Send';
$lang['send_me_message'] = 'Send me an SMS';
$lang['send_to_mobile'] = 'Send to mobile';
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
$lang['sure_ask'] = 'Are you sure ?';

#T
$lang['template_saved'] = 'Template saved';
$lang['test'] = 'Test';
$lang['test_message'] = 'Test message from %s';
$lang['title'] = 'Displayed title';
$lang['title_enternumber_templates'] = '&quot;Enter Your Number&quot; template';
$lang['title_entertext_templates'] = '&quot;Enter Your Message&quot; template';

#U
$lang['username'] = 'Username';

#V
$lang['value'] = 'Value';


#W


#X


#Y


#Z


?>
