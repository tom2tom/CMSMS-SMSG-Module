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

  private $_module;
  private $_num;
  private $_fromnum;
  private $_msg;
  private $_statusmsg;
  protected $_use_curl;
  protected $_status;
  protected $_smsid;


  function __construct(&$module)
  {
    $this->_module = $module;
	self::reset();
  }

  /**
  reset:
  Clear all cached data
  */
  public function reset()
  {
    $this->_num = '';
    $this->_fromnum = '';
    $this->_msg = '';
    $this->_use_curl = 0;
    $this->_status = self::STAT_NOTSENT;
    $this->_statusmsg = '';
  }

  public function use_curl($flag = TRUE)
  {
    $this->_use_curl = ( $flag ) ? 1 : 0;
  }

  /**
  set_msg:
  @msg: the body-content of the message to be sent, including only SMS-valid
   characters, and of suitable length
  */
  public function set_msg($msg)
  {
    $this->_msg = $msg;
  }


  protected function get_msg()
  {
    return $this->_msg;
  }

  /**
  set_num:
  @num: destination phone-number (or separated numbers, if the gateway supports
   batching) appropriately formatted for the gateway
  */
  public function set_num($num)
  {
    $this->_num = $num;
  }


  protected function get_num()
  {
    return $this->_num;
  }

  /**
  set_from:
  @from: the source phone-number (if the gateway supports that) appropriately formatted
  */
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

  /**
  get_status:
  Returns string describing (short-form) status, one of the const's defined above
  */
  public function get_status()
  {
    return $this->_status;
  }

  /**
  get_statusmsg:
  Returns string ...
  */
  public function get_statusmsg()
  {
    return $this->_statusmsg;
  }

  /**
  send:
  Initiate the message transmission (after all relevant parameters are set up)
  */
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

  //for internal use only
  //get parameter stored (for some gateways) when operation result-message was parsed
  public function get_smsid()
  {
    return $this->_smsid;
  }

  protected function &get_module()
  {
    return $this->_module;
  }

  //performs the send, may need to be over-ridden in some gateway-specific subclasses
  protected function _command($cmd)
  {
    $this->_check_curl();
    $res = '';
    if( $this->_use_curl == 0 )
      $res = $this->_send_fopen($cmd);
    else
      $res = $this->_send_curl($cmd);
    debug_to_log('cgsms_sender_base - command = '.$cmd.' res = '.$res);
    return $res;
  }

  private function _send_fopen($cmd)
  {
    $res = '';
    $fh = @fopen ($cmd, 'r');
    if ($fh) {
      while ($line = @fgets($fh,1024)) {
       $res .= $line;
      }
      fclose ($fh);
      return $res;
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
//  if ($this->curl_use_proxy) {
//    curl_setopt ($ch, CURLOPT_PROXY, $this->curl_proxy);
//    curl_setopt ($ch, CURLOPT_PROXYUSERPWD, $this->curl_proxyuserpwd);
//  }
    $res = curl_exec ($ch);
    curl_close ($ch);
    return $res;
  }

  private function _check_curl()
  {
    if( $this->_use_curl == FALSE )
      {
        if( extension_loaded('curl') )
          $this->_use_curl = 1;
      }
  }

  /**
  process_delivery_report:
  */
  public function process_delivery_report()
  {
    return $this->_process_delivery_report();
  }

  /**
  get_name:
  Returns string which is the (un-translated) gateway identifier
  */
  abstract public function get_name();

  /**
  get_description:
  Returns string which is a (translated) brief description of the gateway
  */
  abstract public function get_description();

  /**
  support_custom_sender:
  Returns boolean TRUE/FALSE according to whether the gateway allows use of
  a user-specified source-phone-number (which might need to be a number pre-arranged
  with the gateway supplier)
  */
  abstract public function support_custom_sender();

  /**
  require_country_prefix:
  Returns boolean TRUE/FALSE according to whether a country-code prefix for
  must be prepended to destination phone-numbers
  */
  abstract public function require_country_prefix();

  /**
  require_plus_prefix:
  Returns boolean TRUE/FALSE according to whether the country-code prefix for
  destination numbers must begin with a '+' character
  */
  abstract public function require_plus_prefix();

  /**
  multi_number_separator:
  Returns string containing character to be used for separating numbers in
  multi-destination 'to' values, or FALSE if only single-destination is allowed
  */
  abstract public function multi_number_separator();

  /**
  support_mms:
  Returns boolean TRUE/FALSE according to whether the gateway supports automatic
  partitioning of messages longer than 160 chars into a series of shorter parts
  */
  abstract public function support_mms();

  abstract protected function setup();

  abstract protected function prep_command();

  abstract protected function parse_result($str);

  abstract protected function _process_delivery_report();

  //for internal use only
  //setup and display gateway-specific details for defaultadmin action
  abstract public function get_setup_form();

  //for internal use only
  //process gateway-specific details after 'submit' in defaultadmin action
  abstract public function handle_setup_form($params);

  /**
  get_raw_status:
  */
  abstract public function get_raw_status();
} // end of class
#
# EOF
#
?>
