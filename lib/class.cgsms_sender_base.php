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

abstract class cgsms_sender_base
{
  const STAT_OK = 'sms_sent';
  const STAT_NOTSENT = 'sms_notsent';
  const STAT_ERROR_OTHER = 'sms_error_other';
  const STAT_ERROR_AUTH = 'sms_error_auth';
  const STAT_ERROR_LIMIT = 'sms_error_limit';
  const STAT_ERROR_INVALID_DATA = 'sms_error_invalid_data';
  const STAT_ERROR_BLOCKED = 'sms_error_blocked_number';
  const DELIVERY_OK = 'sms_delivery_ok';
  const DELIVERY_PENDING = 'sms_delivery_pending';
  const DELIVERY_INVALID = 'sms_delivery_invalid';
  const DELIVERY_UNKNOWN = 'sms_delivery_unknown';
  const DELIVERY_BILLING = 'sms_delivery_billing';
  const DELIVERY_OTHER   = 'sms_delivery_other';

  private $_msg;
  private $_num;
  private $_fromnum;
  private $_module;
  private $_statusmsg;
  protected $_use_curl;
  protected $_status;
  protected $_smsid;


  function __construct(&$module)
  {
    $this->_num = '';
    $this->_msg = '';
    $this->_module = $module;
    $this->_use_curl = '';
    $this->_status = self::STAT_NOTSENT;
    $this->_statusmsg = '';
  }


  public function reset()
  {
    $this->_num = '';
    $this->_msg = '';
    $this->_use_curl = '';
    $this->_status = self::STAT_NOTSENT;
    $this->_statusmsg = '';
  }


  public function use_curl($flag = true)
  {
    if( $flag )
      $this->_use_curl = 1;
    else
      $this->_use_curl = 0;
  }


  public function set_msg($msg)
  {
    $this->_msg = $msg;
  }


  protected function get_msg()
  {
    return $this->_msg;
  }


  public function set_num($num)
  {
    $this->_num = $num;
  }


  protected function get_num()
  {
    return $this->_num;
  }

  public function set_from($from)
  {
    if($from)
      $this->_fromnum = $from;
    else
      $this->_fromnum = FALSE;
  }

  protected function get_from()
  {
    return $this->_fromnum;
  }

  protected function set_status($stat)
  {
    $this->_status = $stat;
  }


  public function get_status()
  {
    return $this->_status;
  }


  public function get_statusmsg()
  {
    return $this->_statusmsg;
  }


  public function send()
  {
    $this->_smsid = '';

    // check to make sure we have necessary data.
    $this->setup();
    if( $this->_num == '' || $this->_msg == '' )
      {
	$this->_status = self::STAT_ERROR_INVALID_DATA;
	return FALSE;
      }

    if( !cgsms_utils::ip_can_send(getenv('REMOTE_ADDR')) )
      {
	$this->_status = self::STAT_ERROR_LIMIT;
	return FALSE;
      }

    // next prepare the output.
    $cmd = $this->prep_command();
    if( $cmd === FALSE || $cmd == '' )
      {
	$this->_status = self::STAT_ERROR_INVALID_DATA;
	return FALSE;
      }

    // send it.
    $res = $this->_command($cmd);

	// interpret result.
	$this->parse_result($res);
	$this->_statusmsg = cgsms_utils::get_msg($this,$this->_num,$this->_status,$this->_msg,$this->get_raw_status());
	$success = ($this->_status == self::STAT_OK);
	if( $success )
	  {
	    cgsms_utils::log_send(getenv('REMOTE_ADDR'),$this->_num,$this->_msg);
		audit('',$this->get_module()->GetName(),$this->_statusmsg);
	  }
	return $success;
  }


  public function get_smsid()
  {
    return $this->_smsid;
  }


  protected function &get_module()
  {
    return $this->_module;
  }


  protected function _command($cmd)
  {
    $this->_check_curl();
    $res = '';
    if( $this->_use_curl == 0 )
      {
	$res = $this->_send_fopen($cmd);
      }
    else
      {
	$res = $this->_send_curl($cmd);
      }
    debug_to_log('cgsms_sender_base - command = '.$cmd.' res = '.$res);
    return $res;
  }


  private function _send_fopen($cmd)
  {
    $result = '';
    $fh = @fopen ($cmd, 'r');
    if ($fh) {
      while ($line = @fgets($fh,1024)) {
	$result .= $line;
      }
      fclose ($fh);
      return $result;
    } else {
      return FALSE;
    }
  }


  private function _send_curl($cmd)
  {
    $ch = curl_init ($cmd);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER,0);
//     if ($this->curl_use_proxy) {
//       curl_setopt ($ch, CURLOPT_PROXY, $this->curl_proxy);
//       curl_setopt ($ch, CURLOPT_PROXYUSERPWD, $this->curl_proxyuserpwd);
//     }
    $result=curl_exec ($ch);
    curl_close ($ch);
    return $result;
  }


  private function _check_curl()
  {
    if( $this->_use_curl == '' )
      {
	$this->_use_curl = 0;
	if( extension_loaded('curl') )
	  {
	    $this->_use_curl = 1;
	  }
      }
  }

  public function process_delivery_report()
  {
    return $this->_process_delivery_report();
  }

  abstract public function get_name();

  abstract public function get_description();

  abstract public function support_custom_sender();

  abstract public function require_country_prefix();

  abstract public function require_plus_prefix();

  abstract public function multi_number_separator();

  abstract protected function setup();

  abstract protected function prep_command();

  abstract protected function parse_result($str);

  abstract protected function _process_delivery_report();

  abstract public function get_setup_form();

  abstract public function handle_setup_form($params);

  abstract public function get_raw_status();
} // end of class
#
# EOF
#
?>
