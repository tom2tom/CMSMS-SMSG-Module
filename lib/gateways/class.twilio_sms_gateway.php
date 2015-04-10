<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class twilio_sms_gateway extends sms_gateway_base
{
	const TWILIO_API_URL = 'https://www.twilio.com/docs/api';
	private $_rawstatus;

	public function get_name()
	{
		return 'Twilio';
	}

	public function get_alias()
	{
		return 'twilio';
	}

	public function get_description()
	{
		return $this->_module->Lang('description_twilio');
	}

	public function support_custom_sender()
	{
		return FALSE; //only account-specific from-numbers are allowed
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
		return TRUE;
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
			$module = $this->_module;
			//setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			//none of the apiname's is actually used (indicated by '_' prefix)
			smsg_utils::setprops($gid,array(
			 array($module->Lang('account'),'_account',NULL,0),
			 array($module->Lang('token'),'_token',NULL,1),
			 array($module->Lang('from'),'_from',NULL,0)
			));
		}
		return $gid;
	}

	public function custom_setup(&$smarty,$padm)
	{
		foreach($smarty->tpl_vars['data']->value as &$ob)
		{
			if($ob->signature == '_account'
			|| $ob->signature == '_token')
				$ob->size = 32;
		}
		unset($ob);
		if($padm)
		{
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $this->_module->Lang('help_urlcheck',self::TWILIO_API_URL,self::get_name().' API');
			$smarty->assign('help',$help);
		}
	}

	public function custom_save(&$params)
	{
	}

	protected function setup()
	{
		require_once cms_join_path(dirname(__FILE__),'twilio','Twilio.php');
	}

	protected function prep_command()
	{
		return 'good'; //anything which passes upstream test
	}

	//returns object: Services_Twilio_Rest_Message or Services_Twilio_RestException, or FALSE
	protected function _command($dummy)
	{
		$to = $this->_num;
		$body = strip_tags($this->_msg);
		if( !self::support_mms() )
			$body = substr($body,0,160);
		if( !$to || !smsg_utils::text_is_valid($body,0) )
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$from = 'TODO';
		if(!$from)
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$gid = parent::get_gateid(self::get_alias());
		$parms = smsg_utils::getprops($this->_module,$gid);
		$ob = new Services_Twilio(
		 $parms['_account']['value'],
		 $parms['_token']['value']
		);

		try
		{
			//send it NOTE these array keys must be capitalised
			return $ob->account->messages->create(array(
			 'From' => $from,'To' => $to,'Body' => $body));
		}
		catch (Services_Twilio_RestException $e)
		{
			return $e;
		}
	}

	//$ob = object Services_Twilio_Rest_Message object or Services_Twilio_RestException, or FALSE
	protected function parse_result($ob)
	{
		if (!$ob)
		{
			$this->_rawstatus = '';
			//$this->_status set in self::_command()
			return;
		}
		elseif(get_class($ob) == 'Services_Twilio_Rest_Message')
		{
			if($ob->error_code)
			{
				$this->_rawstatus = $ob->error_message;
				$code = (int)$ob->error_code;
			}
			else
			{
				$this->_rawstatus = '';
				$code = 0;
			}
		}
		else //Services_Twilio_RestException
		{
			$this->_rawstatus = $ob->getMessage();
			$code = $ob->getCode();
		}
		//see https://www.twilio.com/docs/errors/reference
		switch ($code)
		{
		 case 0:
			$this->_status = parent::STAT_OK;
			break;
		 case 20003:
		 case 20403:
			$this->_status = parent::STAT_ERROR_AUTH;
			break;
		 case 11100:
		 case 14101:
		 case 14102:
		 case 14103:
		 case 21601:
		 case 21602:
		 case 21603:
		 case 21604:
		 case 21605:
		 case 21606:
		 case 21607:
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			break;
		 case 21612:
		 case 22001:
			$this->_status = parent::STAT_NOTSENT;
			break;
		 case 14107:
			$this->_status = parent::STAT_ERROR_LIMIT;
			break;
		 case 21610:
		 case 30004:
			$this->_status = parent::STAT_ERROR_BLOCKED;
			break;
		 default:
			$this->_status = parent::STAT_ERROR_OTHER;
			break;
		}
	}

	public function process_delivery_report()
	{
		//TODO must parse $_REQUEST directly
		$smsto = '';
		$smsid = '';
		$status = parent::DELIVERY_UNKNOWN; //or whatever
		return smsg_utils::get_delivery_msg($this->_module,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
} // end of class

?>
