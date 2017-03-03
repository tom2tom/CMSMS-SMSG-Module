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
$lang['add_mobile'] = 'Add number';
$lang['add_parameter'] = 'Add parameter';
$lang['add_template'] = 'Add template';
$lang['addedit_mobile'] = 'Add/Edit a Mobile Phone Record';
$lang['apiname'] = 'API name';
$lang['apply'] = 'Apply';
$lang['ask_delete_mobile'] = 'Are you sure you want to delete this mobile phone number from the database?';

#B


#C
$lang['cancel'] = 'Cancel';
$lang['confirm_uninstall']='You\'re sure you want to uninstall the SMS Utility module?';
$lang['custom'] = 'Custom';

#D
$lang['default'] = 'Default';
$lang['default_gateway'] = 'Default gateway';
$lang['default_template_title'] = 'Prototype for new templates';
$lang['default_tip'] = 'default';
$lang['defaultset_tip'] = 'make default';
$lang['delete'] = 'Delete';
$lang['delete_tip'] = 'delete selected parameter(s)';
$lang['deleteone_tip'] = 'delete';
$lang['dflt_enternumber_template'] = 'Default &quot;Enter Your Number&quot; template';
$lang['dflt_entertext_template'] = 'Default &quot;Enter Your Message Text&quot; template';

#E
$lang['edit_tip'] = 'edit';
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
$lang['error_nogatewayfound'] = 'Could not find any SMS gateway';
$lang['error_notfound'] = 'Could not find the requested item';
$lang['error_params'] = 'Parameter error';
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
$lang['help_dnd'] = 'You can change the order by dragging row(s).';
$lang['help_enternumbertemplate'] = 'Applicable only to the enternumber action, this parameter allows specifying a non default enternumber template to create the form with';
$lang['help_entertexttemplate'] = 'Applicable only to the entertext action, this parameter allows specifying a non default entertext template to create the form with';
$lang['help_gatename'] = 'This parameter allows overriding the default gateway';
$lang['help_inline'] = 'This parameter indicates that the form should replace the link (instead of the default content area of the page).  It is not useful with the destpage parameter';
$lang['help_linktext'] = 'This parameter allows overriding the text that is displayed in the link. This parameter has no effect when the urlonly parameter is used.';
$lang['help_smsnum'] = 'Applicable only to the entertext action, this parameter defines the numeric ID of a pre-defined mobile phone number';
$lang['help_smstext'] = 'Applicable only to the enternumber action, this parameter defines the text that will be sent in the sms message. The text is never displayed in the HTML output of the page for security reasons.';
$lang['help_sure'] = 'Be <strong>very sure</strong> about what you\'re doing, before modifying anything except title(s) and/or value(s)!';
$lang['help_urlcheck'] = 'Refer to the <a href="%s">%s</a> for details';
$lang['help_urlonly'] = 'This parameter indicates that instead of a full link only the URL should be echoed, allowing you to build your own link.';
$lang['helptitle'] = 'Help';

#I
$lang['id'] = 'Id';
$lang['info_enternumber_templates'] = 'This template allows website visitors to enter a mobile phone number to receive a predefined text message.';
$lang['info_entertext_templates'] = 'This template allows website visitors to enter a message that will be sent to a predefined mobile phone number.';
$lang['info_smstest'] = <<<EOS
To test whether the settings for the default SMS gateway are correct, you can send a message to the specified mobile phone number.
It may be <strong>several minutes</strong> before the phone receives the message.
EOS;
$lang['info_sysdflt_enternumber_template'] = 'This is the starting point for creating new \'enter number\' templates. Altering this template will have no immediate effect on anything displayed on the website.';
$lang['info_sysdflt_entertext_template'] = 'This is the starting point for creating new \'enter text\' templates. Altering this template will have no immediate effect on anything displayed on the website.';

#J


#K


#L
$lang['login'] = 'Login';

#M
$lang['module_description'] = 'A module to allow sending of SMS messages from within a CMS Made Simple Website';
$lang['msg_rec_deleted'] = 'Mobile Number Deleted';

#N
$lang['name'] = 'Name';
$lang['none'] = 'None';
$lang['nonumbers'] = 'No number is recorded';
$lang['number'] = 'Number';

#O


#P
$lang['password'] = 'Password';
$lang['perm_admin'] = 'Administer SMS Gateways';
$lang['perm_modify'] = 'Modify SMS Gateway Settings';
$lang['perm_templates'] = 'Modify SMS Gateway Templates';
$lang['perm_use'] = 'Use SMS Gateways';
$lang['phone_number'] = 'Phone number';
$lang['phone_numbers'] = 'Phones';
$lang['postinstall'] = $lang['friendlyname'].' module successfully installed, now please ensure that it is configured properly for use, and apply related permissions';
$lang['postuninstall'] = $lang['friendlyname']. ' module successfully removed';
$lang['prompt_daily_limit'] = 'Number of messages that can be sent by one IP address per day';
$lang['prompt_hourly_limit'] = 'Number of messages that can be sent by one IP address per hour';
$lang['prompt_log_delivers'] = 'Record in the admin log information about received gateway-delivery-reports';
$lang['prompt_log_sends'] = 'Keep local records of sent messages';
$lang['prompt_log_retain_days'] = 'Number of days that local records (if enabled) will be retained';
$lang['prompt_master_password'] = 'Pass-phrase for securing sensitive data';

#Q


#R
$lang['reporting_url'] = 'Any gateway can send delivery reports to';
$lang['reset'] = 'Reset';
$lang['reset_tip'] = 'revert to factory default';


#S
$lang['sample'] = 'Sample';
$lang['security_tab_lbl'] = 'Security / Anti-Spam';
$lang['select'] = 'Select';
$lang['send'] = 'Send';
$lang['send_me_message'] = 'Send me an SMS';
$lang['send_to_mobile'] = 'Send to mobile';
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
$lang['taskdescription_clearlog'] = 'Remove old log data';
$lang['template_content'] = 'Content';
$lang['template_saved'] = 'Template saved';
$lang['template_name'] = 'Template name';
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
$lang['yes'] = 'Yes';

#Z

?>
