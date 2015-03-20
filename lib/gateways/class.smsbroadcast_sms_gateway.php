<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
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

class smsbroadcast_sms_gateway extends cgsms_sender_base
{
	private $_rawstatus;

	public function get_name()
	{
		return 'Smsbroadcast';
	}

	public function get_description()
	{
		return $this->get_module()->Lang('smsbroadcast_description');
	}

	public function support_custom_sender()
	{
		return TRUE;
	}

	public function require_country_prefix()
	{
		return FALSE;
	}

	public function require_plus_prefix()
	{
		return FALSE;
	}

	public function get_setup_form()
	{
		$smarty = cmsms()->GetSmarty();
		$mod = $this->get_module();
		$smarty->assign('smsbroadcast_username', $mod->GetPreference('smsbroadcast_username'));
		$tmp = $mod->GetPreference('smsbroadcast_password');
		if($tmp)
		{
			$s = base64_decode(substr($tmp,5));
			$tmp = substr($s,5);
		}
		$smarty->assign('smsbroadcast_password', $tmp);
		$smarty->assign('smsbroadcast_from', $mod->GetPreference('smsbroadcast_from'));
		return $mod->ProcessTemplate('smsbroadcast_setup.tpl');
	}

	public function handle_setup_form($params)
	{
		$mod = $this->get_module();
		if(!empty($params['smsbroadcast_username']))
			$tmp = trim($params['smsbroadcast_username']);
		else
			$tmp = '';
		$mod->SetPreference('smsbroadcast_username',$tmp);
		if(!empty($params['smsbroadcast_password']))
		{
			$s = substr(base64_encode(md5(microtime())),0,5); //obfuscate a bit
			$tmp = $s.base64_encode($s.trim($params['smsbroadcast_password']));
		}
		else
			$tmp = '';
		$mod->SetPreference('smsbroadcast_password',$tmp);
		if(!empty($params['smsbroadcast_from']))
			$tmp = trim($params['smsbroadcast_from']);
		else
			$tmp = '';
		$mod->SetPreference('smsbroadcast_from',$tmp);
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
 		$mod = $this->get_module();

		$user = $mod->GetPreference('smsbroadcast_username');
		$pass = $mod->GetPreference('smsbroadcast_password');
		if($pass)
		{
			$s = base64_decode(substr($pass,5));
			$pass = substr($s,5);
		}
		if(!$user || !$pass)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}

		$to = parent::get_num();
		$text = substr(strip_tags(parent::get_msg()),0,160);
		if(!$to || !$text)
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$source = parent::get_from(); //can be text e.g. 'MyCompany';
		if(!$source)
			$source = $mod->GetPreference('smsbroadcast_from');
		$ref = ''; //'abc123';

		$ch = curl_init('https://api.smsbroadcast.com.au/api-adv.php');
		if(!$ch)
		{
			$this->_status = parent::STAT_ERROR_OTHER;
			return FALSE;
		}

   		$parms = array();
		$parms['username'] = rawurlencode($user);
		$parms['password'] = rawurlencode($pass);
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

	public function _process_delivery_report()
	{
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
} // end of class

?>
