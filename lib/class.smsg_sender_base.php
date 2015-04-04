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

abstract class smsg_sender_base
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

    if( !smsg_utils::ip_can_send(getenv('REMOTE_ADDR')) )
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
    $this->_statusmsg = smsg_utils::get_msg($this,$this->_num,$this->_status,$this->_msg,$this->get_raw_status());
    $success = ($this->_status == self::STAT_OK);
    if( $success )
      {
        smsg_utils::log_send(getenv('REMOTE_ADDR'),$this->_num,$this->_msg);
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
    debug_to_log('smsg_sender_base - command = '.$cmd.' res = '.$res);
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
  get_setup_form:
  Returns string, xhtml for echo into admin display
  */
  public function get_setup_form()
  {
    $module = self::get_module();
    $padm = $module->CheckPermission('AdministerSMSGateways');
    $db = $module->GetDb();
    $pref = cms_db_prefix();
    $query = 'SELECT * FROM '.$pref.'module_smsg_gates WHERE alias=?';
    if( !$padm )
        $query .= ' AND enabled=1';
    $alias = $this->get_alias();
    $gdata = $db->GetRow($query,array($alias));
    if(!$gdata)
        return '';

    $smarty = cmsms()->GetSmarty();
    $smarty->assign('gatetitle',$module->Lang('frame_title',$gdata['title']));
    $parms = array();
    $query = 'SELECT gate_id,title,value,apiname,apiconvert FROM '.$pref.'module_smsg_props WHERE gate_id=?';
    if( !$padm )
        $query .= ' AND active=1';
    $query .= ' ORDER BY apiorder';
    $gid = (int)$gdata['gate_id'];
    $res = $db->GetAll($query,array($gid));
    if( $res )
      {
        foreach( $res as &$row )
          {
            $ob = (object)$row;
            //adjustments
            if( (int)$ob->apiconvert >= SMSG::DATA_PW ) //parameter is password
              {
                if( $module->havemcrypt ) //en/de-cryption possible
                  {
                    $e = new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS);
                    $ob->value = $e->decrypt($ob->value,$module->GetPreference('masterpass')); //TODO dialog for user input
                  }
                $ob->pass = TRUE;
              }
            unset($ob->apiconvert);
            $parms[] = $ob;
          }
        unset($row);
      }
    if($parms)
      {
        $smarty->assign('space',$alias); //for gateway-data 'namespace'
        $smarty->assign('gateid',$gid);
        $smarty->assign('dcount',count($parms));
      }
    else
      {
        $ob = new stdClass();
        $ob->title = $module->Lang('error_nodatafound');
        $ob->apiname = FALSE; //prevent input-object creation
        $parms[] = $ob;
        $smarty->assign('dcount',0);
      }
    $smarty->assign('data',$parms);
    if( $padm )
      {
        $smarty->assign('titletitle',$module->Lang('title'));
        $smarty->assign('titlevalue',$module->Lang('value'));
        $smarty->assign('titleapiname',$module->Lang('apiname'));
        $smarty->assign('titlehelp',$module->Lang('helptitle'));
        $smarty->assign('titleselect',$module->Lang('select'));
        $smarty->assign('help',
         $module->Lang('help_dnd').'<br />'.$module->Lang('help_sure'));
          $id = $smarty->tpl_vars['actionid']->value;
        $text = $module->Lang('add_parameter');
        $smarty->assign('additem',$module->CreateImageLink($id,$alias.'~add',
         '',$text,'icons/system/newobject.gif',array(),'systemicon','',FALSE));
        $smarty->assign('btndelete',$module->CreateInputSubmit($id,$alias.'~delete',
         $module->Lang('delete'),'title="'.$module->Lang('delete_tip').
         '" onclick="if(row_selected(event,this)) {return confirm(\''.$module->Lang('sure_ask').'\');} else {return false;}"')); //TODO js
      }
    // anything else to set up for the template
    $this->custom_setup($smarty,$padm); //e.g. each $ob->size

    $tpl = ($padm) ? 'gatedata_admin.tpl' : 'gatedata.tpl';
    return $module->ProcessTemplate($tpl);
  }

  /**
  handle_setup_form:
  @params: array of paramters provided after admin form 'submit'
  Parses relevant @params into stored data
  */
  public function handle_setup_form($params)
  {
    $alias = $this->get_alias();
    $module = self::get_module();
    $db = $module->GetDb();
    $pref = cms_db_prefix();
    //TODO upsert needed
    $sql1 = 'UPDATE '.$pref.'module_smsg_props SET ';
    $sql2 = '=? WHERE gate_id=? AND apiname=?';

    $gid = (int)$params[$alias.'~gate_id'];
    unset($params[$alias.'~gate_id']);
    foreach( $params as $key=>&$val )
      {
        if( strpos($key,$alias) === 0 )
          {
            $parts = explode('~',$key);
            if( $parts[2] && $parts[2] != 'check' )
              {
$adbg = $db->Prepare($sql1.$parts[2].$sql2);
$this->DoNothing();
  
               if( $parts[2] == 'apiname' )
                 {
                 //TODO injection check for $parts[1]
                 }
               elseif(0 && $val) //TODO test for password
                 {
                 $e = ( $module->havemcrypt ) ?
                   new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS) : FALSE;
                 $pw = $module->GetPreference('masterpass');
                 if( $e && $pw ) $val = $e->encrypt($val,$pw);
                 }
               $db->Execute($sql1.$parts[2].$sql2,array($val,$gid,$parts[1]));
              }
          }
      }
    unset($val);
    $this->custom_save($params);
  }

  //For internal use only
  //Record or update gateway-specific details in the module's database tables
  //Returns key-value of the row added to the gates-table, for the gateway
  abstract public function upsert_tables();

  //For internal use only
  //Setup gateway-specific details for defaultadmin action
  //$padm = boolean, TRUE if current user has AdministerSMSGateways permission
  abstract public function custom_setup(&$smarty,$padm);

  //For internal use only
  //Process gateway-specific details after 'submit' in defaultadmin action
  abstract public function custom_save(&$params);

  /**
  get_name:
  Returns string which is the (un-translated) gateway identifier
  */
  abstract public function get_name();

  /**
  get_alias:
  Returns string which is the (un-translated) gateway alias, used for classname etc
  */
  abstract public function get_alias();

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

  //Perform pre-message-send initialisation or checks, if any
  //Returns nothing
  abstract protected function setup();

  //Construct the actual string to be sent to the gateway
  //Returns that string, or else empty string or literal FALSE upon failure
  //May return dummy e.g. 'good' or ' ' if such string isn't needed
  abstract protected function prep_command();

  //Interpret $res (string or object, usually) returned from last message-send process
  abstract protected function parse_result($res);

  /**
  process_delivery_report:
  Interpret message-delivery report and return resultant string, suitable for
  public display or logging
  */
  abstract public function process_delivery_report();

  //For internal use only
  //Get string returned by gateway in response to message-send process
  abstract public function get_raw_status();
} // end of class
#
# EOF
#
?>
