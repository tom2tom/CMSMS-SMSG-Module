<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (c) 2010 by Robert Campbell 
#         (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to provide the ability for other
#  modules to send SMS messages
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE

class twilio_sms_gateway extends cgsms_sender_base
{
  private $_rawstatus;

	privaate function __construct()
	{
		require cms_join_path(dirname(__FILE__),'twilio','Twilio.php');
	}

  public function get_name()
  {
    return 'Twilio';
  }

  public function get_description()
  {
    return $this->get_module()->Lang('description_twilio');
  }

  public function get_setup_form()
  {
    $smarty = cmsms()->GetSmarty();
    $mod = $this->get_module();
		//TODO de-obfuscate
    $smarty->assign('username', $mod->GetPreference('twilio_username'));
    $smarty->assign('token', $mod->GetPreference('twilio_token'));
    return $mod->ProcessTemplate('twilio_setup.tpl');
  }

  public function handle_setup_form($params)
  {
		$mod = $this->get_module();
		//TODO obfuscate data
		if( isset($params['username']) )
			$mod->SetPreference('twilio_username',trim($params['username']));
		if( isset($params['token']) )
			$mod->SetPreference('twilio_token',trim($params['token']));
  }

  public function send()
	{
		$this->_smsid = '';

		if( $this->_num == '' || $this->_msg == '' )
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		$body = substr(strip_tags($this->_msg()),0,160);
    if($body == '')
		{
			$this->_status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}
		if( !cgsms_utils::ip_can_send(getenv('REMOTE_ADDR')) )
		{
			$this->_status = parent::STAT_ERROR_LIMIT;
			return FALSE;
		}
		$mod = $this->get_module();
		//TODO de-obfuscate
		$account = $mod->GetPreference('twilio_username');
		$token = $mod->GetPreference('twilio_token');
		if(!$account || !$token)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		$parms = array();
		if($this->_fromnum)
			 $parms['From'] = $this->_fromnum;
		$parms['To'] = $this->_num;
		$parms['Body'] = $body;
		
		// send it
		$client = new Services_Twilio($account,$token);
		try {
			$res = $client->account->messages->create($parms);
		} catch (Services_Twilio_RestException $e) {
			$this->_rawstatus = $e->getMessage();
		}
/*
		if( $res )
		{
			$this->parse_result($res);
			$this->_statusmsg = cgsms_utils::get_msg($this,$this->_num,$this->_status,$this->_msg,$this->get_raw_status());
			if( $this->_status == self::STAT_OK )
			{
				cgsms_utils::log_send(getenv('REMOTE_ADDR'),$this->_num,$this->_msg);
			}
			audit('',$this->get_module()->GetName(),$this->_statusmsg);
*/
			return TRUE;
		}

		return FALSE;
	}

  protected function parse_result($str)
  {
    $this->_rawstatus = $str;
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
