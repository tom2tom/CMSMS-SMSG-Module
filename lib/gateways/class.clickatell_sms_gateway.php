<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (C) 2010 by Robert Campbell (calguy1000@cmsmadesimple.org)
# An addon module for CMS Made Simple to provide the ability for other
# modules to send SMS messages
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
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
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
/*
		 one-step
		 $url = self::CTELL_HTTP_GATEWAY."/sendmsg?user=$user&password=$password&api_id=$api&to=$to&text=$body";
*/
		return ' ';
	}
	
	public function send()
	{
		$this->_smsid = '';
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
		if(!cgsms_utils::ip_can_send(getenv('REMOTE_ADDR')))
		{
			$this->_status = parent::STAT_ERROR_LIMIT;
			return FALSE;
		}
		$mod = $this->get_module();
		$user = $mod->GetPreference('ctell_username');
		$api = $mod->GetPreference('ctell_apiid');
		$password = $mod->GetPreference('ctell_password');
		if($password)
		{
			$s = base64_decode(substr($password,5));
			$password = substr($s,5);
		}
		if(!$user || !$password || !$api)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		// auth call
		$url = self::CTELL_HTTP_GATEWAY."/auth?user=$user&password=$password&api_id=$api";
		// do it
		$ret = file($url);
		// explode response - return string is on first line of the data returned
		$sess = explode(':',$ret[0]);
		if($sess[0] == 'OK')
		{
			$sess_id = trim($sess[1]); // remove any whitespace
			$body = urlencode($body);
			// sendmsg call
			$url = self::CTELL_HTTP_GATEWAY."/sendmsg?session_id=$sess_id&to=$to&text=$body";
			// do it
			$ret = file($url);
			$send = explode(':',$ret[0]);
			if($send[0] == 'ID')
			{
				$this->_rawstatus = $mod->Lang('clickatell_success',$send[1],$to);
				$this->_status == parent::STAT_OK;
				//TODO log
				return TRUE;
			}
			else
			{
				$this->_rawstatus = $mod->Lang('clickatell_fail',$to);
				$this->_status == parent::STAT_ERROR_OTHER;
				return FALSE;
			}
		}
		else
		{
			$this->_rawstatus = $mod->Lang('clickatell_auth',$ret[0]);
			$this->_status == parent::STAT_ERROR_AUTH;
			return FALSE;
		}
	}

	protected function parse_result($send)
	{
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

