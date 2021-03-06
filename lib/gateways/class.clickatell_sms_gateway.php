<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
namespace SMSG\gateways;

class clickatell_sms_gateway extends \SMSG\base_sms_gateway
{
	const CTELL_HTTP_GATEWAY = 'https://api.clickatell.com/http';
	const CTELL_API_URL = 'https://www.clickatell.com/apis-scripts/apis/http-s';

	private $rawstatus;

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
		return $this->mod->Lang('description_clickatell');
	}

	public function support_custom_sender()
	{
		return TRUE; //BUT only registered/approved/purchased numbers TODO determine if any have been, report that
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
		$gid = \SMSG\Utils::setgate($this);
		if ($gid) {
			parent::set_gateid($gid);
			$mod = $this->mod;
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			\SMSG\Utils::setprops($gid,[
			 [$mod->Lang('username'),'user',NULL,0],
			 [$mod->Lang('password'),'password',NULL,1],
			 [$mod->Lang('apiid'),'api_id',NULL,0]
			]);
		}
		return $gid;
	}

	public function custom_setup(&$tplvars,$padm)
	{
		foreach ($tplvars['data'] as &$ob) {
			if ($ob->signature == 'password') {
				$ob->size = 20;
				break;
			}
		}
		unset($ob);
		if ($padm) {
			$tplvars['help'] .= '<br />'.
				$this->mod->Lang('help_urlcheck',self::CTELL_API_URL,self::get_name().' API');
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
		$parms = \SMSG\Utils::getprops($this->mod,$gid);
		if (
		 $parms['user']['value'] == FALSE ||
		 $parms['password']['value'] == FALSE ||
		 $parms['api_id']['value'] == FALSE
		) {
			$this->status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		if ($this->num == FALSE) {
			$this->status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		$text = strip_tags($this->msg);
		if (!self::support_mms()) {
			$text = substr($text,0,160);
		}
		if (!\SMSG\Utils::text_is_valid($text,0)) {
			$this->status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$sends = [];
		foreach ($parms as &$val) {
			$sends[$val['apiname']] = $val['value']; //CHECKME urlencode ?
		}
		unset($val);

		$sends['to'] = $this->num;
/*
from = international format number, registered and approved
callback to registered URL
concat 2 or 3 joined messages
req_feat
queue 1,2,3 1=highest priority, 3=default
*/
		$sends['text'] = urlencode($text);

		$str = \SMSG\Utils::implode_with_key($sends);
		$str = self::CTELL_HTTP_GATEWAY.'/sendmsg?'.str_replace('amp;','',$str);
		return $str;
	}

	/*
	For message to one number, returns
	ID: apimsgid
	or:
	ERR: Error number, error description

	For message to more than one number, returns
	ID: apimsgid To: xxxxxx
	ID: apimsgid To: xxxxxx
	or:
	ERR: Error number, error description To: destination address
	ERR: Error number, error description To: destination address

	Error numbers:
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
	protected function parse_result($str)
	{
		$this->rawstatus = $str;

		$parts = explode(':',$str);
		if ($parts[0] == 'ID') {
			$this->status = parent::STAT_OK;
			$this->smsid = trim($parts[1]);
		} else {
			$parts = explode(',',$parts[1]);
			$code = trim($parts[0]) + 0;
			switch($code) {
			 case 1:
			 case 2:
			 case 7:
			 case 103:
			 case 108:
				$this->status = parent::STAT_ERROR_AUTH;
				break;
			 case 101:
			 case 106:
			 case 107:
			 case 113:
			 case 116:
				$this->status = parent::STAT_ERROR_INVALID_DATA;
				break;
			 case 121:
				$this->status = parent::STAT_ERROR_BLOCKED;
				break;
			 case 130:
				$this->status = parent::STAT_ERROR_LIMIT;
				break;
			 default:
				$this->status = parent::STAT_ERROR_OTHER;
				break;
			}
		}
	}

	/*
	Must parse $_REQUEST directly
	Gateway returns: api_id, apiMsgId, cliMsgId, to, timestamp, from, status, charge
	Sample request:
	http://www.yoururl.com?api_id=12345&apiMsgId=996f364775e24b8432f45d77da8eca47
	&cliMsgId=abc123&timestamp=1218007814&to=279995631564&from=27833001171&status=003&charge=0.300000
	Status codes:
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
	public function process_delivery_report()
	{
		switch ((int)$_REQUEST['status']) {
		 case 3:
		 case 4:
		 case 8:
			$status = parent::DELIVERY_OK;
			break;
		 case 1:
		 case 5:
		 case 7:
 		 case 9:
 		 case 10:
 		 case 14:
			$status = parent::DELIVERY_INVALID;
			break;
		 case 12:
			$status = parent::DELIVERY_BILLING;
			break;
		 case 2:
		 case 11:
			$status = parent::DELIVERY_PENDING;
			break;
		 default:
			$status = parent::DELIVERY_UNKNOWN;
			break;
		}
		$smsid = $_REQUEST['apiMsgId'];
		$smsto = $_REQUEST['to'];
		return \SMSG\Utils::get_delivery_msg($this->mod,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->rawstatus;
	}
}
