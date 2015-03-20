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
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this file; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE

class twilio_sms_gateway extends cgsms_sender_base
{
	private $_rawstatus;

	public function get_name()
	{
		return 'Twilio';
	}

	public function get_description()
	{
		return $this->get_module()->Lang('twilio_description');
	}

	public function support_custom_sender()
	{
		return FALSE; //TODO
	}

	public function require_country_prefix()
	{
		return TRUE; //TODO
	}

	public function require_plus_prefix()
	{
		return TRUE;
	}

	public function get_setup_form()
	{
		$smarty = cmsms()->GetSmarty();
		$mod = $this->get_module();
		$smarty->assign('twilio_username', $mod->GetPreference('twilio_username'));
		$tmp = $mod->GetPreference('twilio_password');
		if($tmp)
		{
			$s = base64_decode(substr($tmp,5));
			$tmp = substr($s,5);
		}
		$smarty->assign('twilio_password', $tmp);
		$smarty->assign('twilio_from', $mod->GetPreference('twilio_from'));
		return $mod->ProcessTemplate('twilio_setup.tpl');
	}

	public function handle_setup_form($params)
	{
		$mod = $this->get_module();
		if(!empty($params['twilio_username']))
			$tmp = trim($params['twilio_username']);
		else
			$tmp = '';
		$mod->SetPreference('twilio_username',$tmp);
		if(!empty($params['twilio_password']))
		{
			$s = substr(base64_encode(md5(microtime())),0,5); //obfuscate a bit
			$tmp = $s.base64_encode($s.trim($params['twilio_password']));
		}
		else
			$tmp = '';
		$mod->SetPreference('twilio_password',$tmp);
		if(!empty($params['twilio_from']))
			$tmp = trim($params['twilio_from']);
		else
			$tmp = '';
		$mod->SetPreference('twilio_from',$tmp);
	}

	protected function setup()
	{
	}

	protected function prep_command()
	{
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
/*
curl may need -k (insecure) option too
curl -X POST https://api.twilio.com/2010-04-01/Accounts/AC7ca93c7b41de5d688a2bbf45bca2550e/SMS/Messages.json \
 -u AC7ca93c7b41de5d688a2bbf45bca2550e:8cfe44235eaea3edc6d2ac5746ed33d4 \
 --data-urlencode "From=+19204826333" \
 --data-urlencode "To=+61417394479" \
 --data-urlencode 'Body=Another message sent via gateway'
*/
		//NB these keys must be capitalised!
		$parms = array(
		 'To' => $to,
		 'From' => $from,
		 'Body' => $body
		);

		require_once cms_join_path(dirname(__FILE__),'twilio','Twilio.php');
		$ob = new Services_Twilio($account,$token);
		try
		{
			//send it
			$res = $ob->account->messages->create($parms);

			$this->parse_result($res);
			$this->_statusmsg = cgsms_utils::get_msg(
				$this,
				$to,
				$this->_status,
				$msg,
				$this->_rawstatus
			);
			if($this->_status == parent::STAT_OK)
			{
				cgsms_utils::log_send(getenv('REMOTE_ADDR'),$this->_num,$this->_msg);
			}
//		audit('',$this->get_module()->GetName(),$this->_statusmsg);
			echo ($this->_statusmsg);
			return TRUE;
		}
		catch (Services_Twilio_RestException $e)
		{
			$this->_rawstatus = $e->getMessage();
			return FALSE;
		}
	}

	//$ob = Services_Twilio_Rest_Message object
	protected function parse_result($ob)
	{
		$this->_rawstatus = ''; //TODO
/*	$this->_rawstatus = $ob->
			date_created => string 'Sun, 01 Mar 2015 05:25:42 +0000'
			date_sent => null
			to => string '+61417394479'
			body => string 'This is a new message via the gateway'
			status => string 'queued'
			error_code => null
			error_message => null
*/
/*
returned json
{
	"sid": "SM75c9b26ec7fb4b2aaf303d8b3540a694",
	"date_created": "Sun, 01 Mar 2015 02:19:06 +0000",
	"date_updated": "Sun, 01 Mar 2015 02:19:06 +0000",
	"date_sent": null,
	"account_sid": "AC7ca93c7b41de5d688a2bbf45bca2550e",
	"to": "+61417394479",
	"from": "+19204826333",
	"body": "Another message sent via gateway",
	"status": "queued",
	"direction": "outbound-api",
	"api_version": "2010-04-01",
	"price": null,
	"price_unit": "USD",
	"uri": "/2010-04-01/Accounts/AC7ca93c7b41de5d688a2bbf45bca2550e/SMS/Messages/SM75c9b26ec7fb4b2aaf303d8b3540a694.json",
	"num_segments": "1"
}

ERROR EXAMPLE RETURNS

'HTTP/1.1 400 BAD REQUEST
Content-Type: application/json; charset=utf-8
Date: Sun, 01 Mar 2015 04:22:31 GMT
X-Powered-By: AT-5000
X-Shenanigans: none
Content-Length: 136
Connection: keep-alive

{"code": 21603,
 "message": "A 'From' phone number is required.",
 "more_info": "https://www.twilio.com/docs/errors/21603",
 "status": 400}

$status = int 400

SUCCESS EXAMPLE RETURNS

HTTP/1.1 201 CREATED
Content-Type: application/json; charset=utf-8
Date: Sun, 01 Mar 2015 04:29:47 GMT
X-Powered-By: AT-5000
X-Shenanigans: none
Content-Length: 777
Connection: keep-alive

{
"sid": "SMf6dd10d695034992a5bc3577241e9697",
"date_created": "Sun, 01 Mar 2015 04:29:47 +0000",
"date_updated": "Sun, 01 Mar 2015 04:29:47 +0000",
"date_sent": null,
"account_sid": "AC7ca93c7b41de5d688a2bbf45bca2550e",
"to": "+61417394479",
"from": "+19204826333",
"body": "This is another test message via the gat'... (length=974)
 ETC see above
}

$status = int 201
*/
		$this->_status == parent::STAT_OK; //TODO
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
