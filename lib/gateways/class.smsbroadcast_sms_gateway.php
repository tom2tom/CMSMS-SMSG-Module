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
		return parent::get_module()->Lang('description_smsbroadcast');
	}

	public function support_custom_sender()
	{
		return TRUE;
	}

	public function support_mms()
	{
		return FALSE; //TODO
	}

	public function require_country_prefix()
	{
		return FALSE;
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
			$module = parent::get_module();
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
			$module = parent::get_module();
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $module->Lang('help_urlcheck',self::SMSBC_API_URL,self::get_name().' API');
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
		$parms = smsg_utils::getprops(parent::get_module(),$gid);
		if($parms['username']['value'] == FALSE ||
		 $parms['password']['value'] == FALSE)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}

		$to = parent::get_num();
		$text = strip_tags(parent::get_msg());
		if( !self::support_mms() )
			$text = substr($text,0,160);
		if(!$to || !smsg_utils::text_is_valid($text,0) )
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$source = parent::get_from(); //can be text e.g. 'MyCompany';
		if(!$source)
			$source = 'TODO';
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
