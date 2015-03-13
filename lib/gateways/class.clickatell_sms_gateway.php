<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (C) 2010 by Robert Campbell (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to provide the ability for other
#  modules to send SMS messages
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (C) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This file is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this file is distributed
# as part of an addon module to CMS Made Simple. You may not use this file
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this file; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE
class clickatell_sms_gateway extends cgsms_sender_base
{
	const CTELL_HTTP_GATEWAY = 'http://api.clickatell.com/http';
	//CHECKME https ?
	private $_rawstatus;

	public function get_name()
	{
		return 'Clickatell';
	}

	public function get_description()
	{
		return $this->get_module()->Lang('clickatell_description');
	}

	public function get_setup_form()
	{
		$smarty = cmsms()->GetSmarty();
		$mod = $this->get_module();

		$smarty->assign('ctell_username', $mod->GetPreference('ctell_username'));
		$smarty->assign('ctell_apiid', $mod->GetPreference('ctell_apiid'));
		$tmp = $mod->GetPreference('ctell_password');
		if($tmp)
		{
			$s = base64_decode(substr($tmp,5));
			$tmp = substr($s,5);
		}
		$smarty->assign('ctell_password', $tmp);
		return $mod->ProcessTemplate('clickatell_setup.tpl');
	}

	public function handle_setup_form($params)
	{
		$mod = $this->get_module();
		if(!empty($params['ctell_username']))
			$tmp = trim($params['ctell_username']);
		else
			$tmp = '';
		$mod->SetPreference('ctell_username',$tmp);
		if(!empty($params['ctell_apiid']))
			$tmp = trim($params['ctell_apiid']);
		else
			$tmp = '';
		$mod->SetPreference('ctell_apiid',$tmp);
		if(!empty($params['ctell_password']))
		{
			$s = substr(base64_encode(md5(microtime())),0,5); //obfuscate a bit
			$tmp = $s.base64_encode($s.trim($params['ctell_password']));
		}
		else
			$tmp = '';
		$mod->SetPreference('ctell_password',$tmp);
	}

	protected function setup()
	{
	}

	protected function prep_command()
	{
		$mod = $this->get_module();
		$parms = array();

		$parms['api_id'] = $mod->GetPreference('ctell_apiid');
		if($parms['api_id'] === '') return FALSE;
		$parms['user'] = $mod->GetPreference('ctell_username');
		if($parms['user'] === '') return FALSE;
		$pass = $mod->GetPreference('ctell_password');
		if($pass)
		{
			$s = base64_decode(substr($pass,5));
			$pass = substr($s,5);
		}
		if($pass === '') return FALSE;
		$parms['password'] = $pass;

		$parms['to'] = parent::get_num();
		if($parms['to'] === '') return FALSE;
/*
from = international format number, registered and approved
callback to registered URL
concat 2 or 3 joined messages
req_feat
queue 1,2,3 1=highest priority, 3=default
*/
		$text = substr(strip_tags(parent::get_msg()),0,160);
		if($text === '') return FALSE;
		$parms['text'] = urlencode($text);

		$str = cge_array::implode_with_key($parms);
		$str = self::CTELL_HTTP_GATEWAY.'/sendmsg?'.str_replace('amp;','',$str);
		return $str;
	}

	protected function parse_result($str)
	{
		$this->_rawstatus = $str;
/*
		to one
		ID: apimsgid
		or:
		ERR: Error number, error description

		to many
		ID: apimsgid To: xxxxxx
		ID: apimsgid To: xxxxxx		
		or:
		ERR: Error number, error description To: destination address
		ERR: Error number, error description To: destination address
		
001 Authentication failed Authentication details are incorrect.
002 Unknown username or password Authorization error, unknown user name or incorrect password.
003 Session ID expired The session ID has expired after a pre-set time of inactivity.
005 Missing session ID Missing session ID attribute in request.
007 IP Lockdown violation You have locked down the API instance to a specific IP address and then sent from an IP address different to the one you set.
101 Invalid or missing parameters One or more required parameters are missing or invalid
102 Invalid user data header The format of the user data header is incorrect.
103 Unknown API message ID The API message ID is unknown. Log in to your API account to check the ID or create a new one.
104 Unknown client message ID The client ID message that you are querying does not exist.
105 Invalid destination address The destination address you are attempting to send to is invalid.
106 Invalid source address The sender address that is specified is incorrect.
107 Empty message The message has no content.
108 Invalid or missing API ID The API message ID is either incorrect or has not been included in the API call.
109 Missing message ID This can be either a client message ID or API message ID. For example when using the stop message command.
113 Maximum message parts exceeded The text message component of the message is greater than the permitted 160 characters (70 Unicode characters). Select concat equal to 1,2,3-N to overcome this by splitting the message across multiple messages.
114 Cannot route message This implies that the gateway is not currently routing messages to this network prefix. Please email support@clickatell.com with t he mobile number in question.
115 Message expired Message has expired before we were able to deliver it to the upstream gateway. No charge applies
116 Invalid Unicode data The format of the unicode data entered is incorrect.
120 Invalid delivery time The format of the delivery time entered is incorrect.
121 Destination mobile number blocked This number is not allowed to receive messages from us and has been put on our block list.
122 Destination mobile opted out The user has opted out and is no longer subscribed to your service.
123 Invalid Sender ID A sender ID needs to be registered and approved before it can be successfully used in message sending.
128 Number delisted This error may be returned when a number has been delisted.
130 Maximum MT limit exceeded until <UNIX TIME STAMP> This error is returned when an account has exceeded the maximum number of MT messages which can be sent daily or monthly. You can send messages again on the date indicated by the UNIX TIMESTAMP.
201 Invalid batch ID The batch ID which you have entered for batch messaging is not valid.
202 No batch template The batch template has not been defined for the batch command.
301 No credit left Insufficient credits
901 Internal error
*/
		$parts = explode(':',$str);
		if($parts[0] == 'ID')
		{
			parent::set_status(self::STAT_OK);
			$this->_smsid = trim($parts[1]);
		}
		else
		{
			$parts = explode(',',$parts[1]);
			$code = trim($parts[0]) + 0;
			switch($code)
			{
			 case 1:
			 case 2:
			 case 7:
			 case 103:
			 case 108:
				parent::set_status(parent::STAT_ERROR_AUTH);
				break;
			 case 101:
			 case 106:
			 case 107:
			 case 113:
			 case 116:
				parent::set_status(parent::STAT_ERROR_INVALID_DATA);
				break;
			 case 121:
				parent::set_status(parent::STAT_ERROR_BLOCKED);
				break;
			 case 130:
				parent::set_status(parent::STAT_ERROR_LIMIT);
				break;
			 default:
				parent::set_status(parent::STAT_ERROR_OTHER);
				break;
			}
		}
	}

	public function _process_delivery_report()
	{
/*
001 0x001 Message unknown The message ID is incorrect or reporting is delayed.
002 0x002 Message queued The message could not be delivered and has been queued for attempted redelivery.
003 0x003 Delivered to gateway Delivered to the upstream gateway or network (delivered to the recipient).
004 0x004 Received by recipient Confirmation of receipt on the handset of the recipient.
005 0x005 Error with message There was an error with the message, probably caused by the content of the message itself.
006 0x006 User cancelled message delivery The message was terminated by a user (stop message command) or by our staff.
007 0x007 Error delivering message An error occurred delivering the message to the handset.
008 0x008 OK Message received by gateway.
009 0x009 Routing error An error occurred while attempting to route the message.
010 0x00A Message expired Message has expired before we were able to deliver it to the upstream gateway. No charge applies.
011 0x00B Message queued for later delivery Message has been queued at the gateway for delivery at a later time (delayed delivery).
012 0x00C Out of credit The message cannot be delivered due to a lack of funds in your account. Please re-purchase credits.
014 0x00E Maximum MT limit exceeded The allowable amount for MT messaging has been exceeded.
*/
		return '';
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
}

?>
