<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: SMSG (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
# An addon module for CMS Made Simple to provide the ability for other
# modules to send SMS messages
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2015 Ted Kulp (wishy@cmsmadesimple.org)
# Its homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This file is free software; you can redistribute it and/or modify it
# under the terms of the GNU Affero General Public License as published
# by the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This file is part of an addon module for CMS Made Simple.
# As a special extension to the AGPL, you may not use this file in any
# non-GPL version of CMS Made Simple, or in any version of CMS Made Simple
# that does not indicate clearly and obviously in its admin section that
# the site was built with CMS Made Simple.
#
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
# Read the Licence online: http://www.gnu.org/licenses/licenses.html#AGPL
#-------------------------------------------------------------------------
#END_LICENSE

class twilio_sms_gateway extends smsg_sender_base
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
		return $this->get_module()->Lang('description_twilio');
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
		$module = parent::get_module();
		$gid = smsg_utils::setgate($module,$this,SMSG::DATA_ASIS);
		//setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=apiconvert
		//none of the apiname's is actually used (indicated by '_' prefix)
		if($gid) smsg_utils::setprops($module,$gid,array(
			array($module->Lang('account'),'_account',NULL,SMSG::DATA_ASIS),
			array($module->Lang('token'),'_token',NULL,SMSG::DATA_PW),
			array($module->Lang('from'),'_from',NULL,SMSG::DATA_ASIS)
			));
		return $gid;
	}

	public function custom_setup(&$smarty,$padm)
	{
		foreach($smarty->tpl_vars['data']->value as &$ob)
		{
			if(!empty($ob->pass) || $ob->apiname == '_account')
				$ob->size = 32;
		}
		unset($ob);
		if($padm)
		{
			$mod = parent::get_module();
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $mod->Lang('help_urlcheck',self::TWILIO_API_URL,self::get_name().' API');
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

	//returns object: Services_Twilio_Rest_Message or Services_Twilio_RestException, or FALSE
	protected function _command($dummy)
	{
		$to = parent::get_num();
		$msg = parent::get_msg();
		if(!$to || !$msg)
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		$body = substr(strip_tags($msg),0,160);
		if($body == '')
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		$mod = parent::get_module();
		$account = $mod->GetPreference('twilio_username');
		$token = $mod->GetPreference('twilio_password');
		if($token)
		{
			$s = base64_decode(substr($token,5));
			$token = substr($s,5);
		}
		if(!$account || !$token)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		$from = $mod->GetPreference('twilio_from');
		if(!$from)
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		require_once cms_join_path(dirname(__FILE__),'twilio','Twilio.php');
		$ob = new Services_Twilio($account,$token);
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
		return ''; //TODO
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
} // end of class

?>
