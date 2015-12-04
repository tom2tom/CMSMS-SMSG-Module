<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class smsbroadcast_sms_gateway extends sms_gateway_base
{
	const SMSBC_API_URL = 'https://www.smsbroadcast.com.au/Advanced%20HTTP%20API.pdf';
	private $_rawstatus;

	public function get_name()
	{
		return 'Smsbroadcast';
	}

	public function get_alias()
	{
		return 'smsbroadcast';
	}

	public function get_description()
	{
		return $this->_module->Lang('description_smsbroadcast');
	}

	public function support_custom_sender()
	{
		return TRUE;
	}

	public function support_mms()
	{
		return TRUE; //TODO send parameter maxsplit (up to 5)
	}

	public function require_country_prefix()
	{
		return TRUE; //actually, preferred but not mandatory
	}

	public function require_plus_prefix()
	{
		return FALSE;
	}

	public function multi_number_separator()
	{
		return ',';
	}

	public function upsert_tables()
	{
		$gid = smsg_utils::setgate($this);
		if($gid)
		{
			parent::set_gateid($gid);
			$module = $this->_module;
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			smsg_utils::setprops($gid,array(
			 array($module->Lang('username'),'username',NULL,0),
			 array($module->Lang('password'),'password',NULL,1),
			 array($module->Lang('from'),'from',NULL,0),
			 array($module->Lang('reference'),'ref',NULL,0)
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
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $this->_module->Lang('help_urlcheck',self::SMSBC_API_URL,self::get_name().' API');
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
		return 'good'; //anything which passes upstream test
	}

	protected function _command($dummy)
	{
		$gid = parent::get_gateid(self::get_alias());
		$parms = smsg_utils::getprops($this->_module,$gid);
		if($parms['username']['value'] == FALSE ||
		   $parms['password']['value'] == FALSE)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}

		$to = $this->_num;
		$text = strip_tags($this->_msg);
		if(!self::support_mms())
			$text = substr($text,0,160);
		if(!$to || !smsg_utils::text_is_valid($text,0))
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$source = $this->_fromnum; //can be text e.g. 'MyCompany';
		$ref = ''; //'abc123';

		$ch = curl_init('https://api.smsbroadcast.com.au/api-adv.php');
		if(!$ch)
		{
			$this->_status = parent::STAT_ERROR_OTHER;
			return FALSE;
		}

		foreach($parms as &$val)
			$val = rawurlencode($val['value']);
		unset($val);

		$parms['to'] = rawurlencode($to);
		if($source)
			$parms['from'] = rawurlencode($source);
		$parms['message'] = rawurlencode($text);
		if($ref)
			$parms['ref'] = rawurlencode($ref);

		$str = cge_array::implode_with_key($parms);
		$str = str_replace('amp;','',$str);

		curl_setopt($ch,CURLOPT_POST,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$str);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	protected function parse_result($str)
	{
		$lines = explode('\n',$str);
		foreach($lines as $oneline)
		{
			$message_data = explode(':',$oneline);
			switch ($message_data[0])
			{
			 case 'OK':
				$this->_rawstatus = '';
				$this->_status = parent::STAT_OK;
				break;
			 case 'BAD':
				$this->_rawstatus = $message_data[2];
				$this->_status = parent::STAT_NOTSENT;
				break;
			 case 'ERROR':
				$this->_rawstatus = $message_data[1];
				$this->_status = parent::STAT_ERROR_OTHER;
				break;
			}		
		}
	}

	/*
	Must parse $_REQUEST directly
	Gateway returns: to, ref, smsref, status
	 to: mobile number the message was sent to, in international format (614xxxxxxxx)
	 ref: sender's reference number, if provided when message was sent
	 smsref: SMS Broadcast reference number as returned by the API when the message was sent
	 status: current status of the message. One of
	  Delivered – The message was successfully delivered
	  Expired – The message could not be delivered within the required time
	  Failed – There was a problem with the message (e.g. incorrect mobile number, or mobile service disconnected)

	Sample request:
	http://www.yoururl.com?to=61400111222&ref=112233&smsref=1122334455&status=Delivered
	*/
	public function process_delivery_report()
	{
		switch ($REQUEST['status'])
		{
		 case 'Delivered':
			$status = parent::DELIVERY_OK;
			break;
		 case 'Failed':
		 case 'Expired':
			$status = parent::DELIVERY_INVALID;
			break;
		 default:
			$status = parent::DELIVERY_UNKNOWN;
			break;
		}
		$smsid = $REQUEST['smsref'];
		$smsto = $REQUEST['to'];
		return smsg_utils::get_delivery_msg($this->_module,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}

}

?>
