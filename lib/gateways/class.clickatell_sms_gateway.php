<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class clickatell_sms_gateway extends sms_gateway_base
{
	const CTELL_HTTP_GATEWAY = 'http://api.clickatell.com/http';
	const CTELL_API_URL = 'https://www.clickatell.com/apis-scripts/apis/http-s';
	//CHECKME https ?
	private $_rawstatus;

	public function get_name()
	{
		return 'Clickatell';
	}

	public function get_alias()
	{
		return 'clickatell';
	}

	public function get_description()
	{
		return $this->get_module()->Lang('description_clickatell');
	}

	public function support_custom_sender()
	{
		return TRUE; //only registered/approved/purchased numbers
	}

	public function support_mms()
	{
		return FALSE; //TODO
	}

	public function require_country_prefix()
	{
		return TRUE;
	}

	public function require_plus_prefix()
	{
		return FALSE;
	}

	public function multi_number_separator()
	{
		return FALSE;
	}

	public function upsert_tables()
	{
		$gid = smsg_utils::setgate($this);
		if($gid)
		{
			parent::set_gateid($gid);
			$module = parent::get_module();
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			smsg_utils::setprops($gid,array(
			 array($module->Lang('username'),'user',NULL,0),
			 array($module->Lang('password'),'password',NULL,1),
			 array($module->Lang('apiid'),'api_id',NULL,0)
			));
		}
		return $gid;
	}

	public function custom_setup(&$smarty,$padm)
	{
		foreach($smarty->tpl_vars['data']->value as &$ob)
		{
			if($ob->signature == 'password')
			{
				$ob->size = 20;
				break;
			}
		}
		unset($ob);
		if($padm)
		{
			$module = parent::get_module();
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $module->Lang('help_urlcheck',self::CTELL_API_URL,self::get_name().' API');
			$smarty->assign('help',$help);
		}
	}

	public function custom_save(&$params)
	{
	}

	protected function setup()
	{
	}

	protected function prep_command()
	{
		$gid = parent::get_gateid(self::get_alias());
		$parms = smsg_utils::getprops($gid);
		if(
		 $parms['user']['value'] == FALSE ||
		 $parms['password']['value'] == FALSE ||
		 $parms['api_id']['value'] == FALSE
		)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}

		$sends = array();
		foreach($parms as &$val)
			$sends[$val['apiname']] = $val['value'];
		unset($val);

		$sends['to'] = parent::get_num();
		if($sends['to'] == FALSE) return FALSE;
/*
from = international format number, registered and approved
callback to registered URL
concat 2 or 3 joined messages
req_feat
queue 1,2,3 1=highest priority, 3=default
*/
		$text = substr(strip_tags(parent::get_msg()),0,160);
		if($text == FALSE) return FALSE;
		$sends['text'] = urlencode($text);

		$str = cge_array::implode_with_key($sends);
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

	public function process_delivery_report()
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
		return ''; //TODO
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
}

?>
