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

class cgsms_utils
{
  public static function get_gateways_full()
  {
	//TODO deprecated Use gateways-table data
    $dir = cms_join_path(dirname(__FILE__),'gateways','');
    $files = glob($dir.'class.*sms_gateway.php');
    if( count($files) == 0 )
      {
	return FALSE;
      }
    
    $objs = array();
    for( $i = 0; $i < count($files); $i++ )
      {
	include_once($files[$i]);

	$classname = str_replace(array($dir,'class.','.php'),array('','',''),$files[$i);
	$obj = new $classname(cge_utils::get_module('CGSMS'));
	$objs[$classname] = array();
	$objs[$classname]['obj'] = $obj;
	$objs[$classname]['name'] = $obj->get_name();
	$objs[$classname]['desc'] = $obj->get_description();
	$objs[$classname]['form'] = $obj->get_setup_form();
      }

    return $objs;
  }


  public static function &get_gateway()
  {
    $module = cge_utils::get_module('CGSMS');
    $classname = $module->GetPreference('sms_gateway');
    if( $classname == '' || $classname == '-1' ) return FALSE;

    $fn = cms_join_path(dirname(__FILE__),'gateways','class.'.$classname.'.php');
    include_once($fn);

    $obj = new $classname($module);
    if( !$obj ) return FALSE;
      
    return $obj;
  }


  public static function get_msg(&$gateway,$num,$stat,$msg,$opt = '')
  {
    $module = cge_utils::get_module('CGSMS');
    $ip = getenv('REMOTE_ADDR');
    $txt = '';
    if( $stat == cgsms_sender_base::STAT_OK )
      {
	$txt = $module->Lang($stat,$msg,$num,$ip,$gateway->get_smsid());
      }
    else if( $stat == cgsms_sender_base::STAT_ERROR_OTHER )
      {
	$txt = $module->Lang($stat,$opt,$msg,$num,$ip,$gateway->get_smsid());
      }
    else if( $stat != cgsms_sender_base::STAT_NOTSENT )
      {
	$txt = $module->Lang($stat,$msg,$num,$ip);
      }
    return $txt;
  }


  public static function get_delivery_msg($gateway,$del_status,$smsid,$smsto)
  {
    $module = cge_utils::get_module('CGSMS');
    $ip = getenv('REMOTE_ADDR');
    $txt = '';

    $txt = $module->Lang($del_status,$smsid,$smsto,$ip);
    return $txt;
  }


  static public function get_reporting_url()
  {
    // get the default page id.
    global $gCms;
    $contentops = $gCms->GetContentOperations();
    $returnid = $contentops->GetDefaultContent();
    $module = cge_utils::get_module('CGSMS');

    $prettyurl = 'CGSMS/devreport';
    $url = $module->CreateURL('cntnt01','devreport',$returnid,array(),false,$prettyurl);
    return $url;
  }

  
  static public function is_valid_phone($number)
  {
    $formats = array('+##########',
		     '+###########',
		     '###-###-####', 
		     '####-###-###',
		     '(###) ###-###', 
		     '####-####-####',
		     '##-###-####-####', 
		     '####-####', 
		     '###-###-###',
		     '#####-###-###', 
		     '##########',
		     '###########');

    $str = ereg_replace('[0-9]','#',$number);
    if( in_array($str,$formats) ) return TRUE;
    return FALSE;
  }


  public static function log_send($ip_address,$mobile,$msg,$statusmsg = '')
  {
    global $gCms;
    $db = $gCms->GetDb();

    $query = 'INSERT INTO '.cms_db_prefix().'module_cgsms_sent
               (mobile,ip,msg,sdate) VALUES (?,?,?,NOW())';
    $db->Execute($query,array($mobile,$ip_address,$msg));
  }

  public static function ip_can_send($ip_address)
  {
    global $gCms;
    $db = $gCms->GetDb();

    $now = $db->DbTimeStamp(time());
    $date1 = $db->DbTimeStamp(time()-3600);
    $date2 = $db->DbTimeStamp(time()-24*3600);
    $query = 'SELECT COUNT(mobile) AS count FROM '.cms_db_prefix()."module_cgsms_sent
               WHERE ip = ? AND (sdate BETWEEN $date1 and $now)";
    $tmp1 = $db->GetOne($query,array($ip_address));

    $module = cge_utils::get_module('CGSMS');
    $hourly = $module->GetPreference('sms_hourlylimit',5);
    if( $tmp1 > $hourly ) return FALSE;

    $query = 'SELECT COUNT(mobile) AS count FROM '.cms_db_prefix()."module_cgsms_sent
               WHERE ip = ? AND (sdate BETWEEN $date2 and $now)";
    $daily = $module->GetPreference('sms_dailylimit',20);
    $tmp2 = $db->GetOne($query,array($ip_address));
    if( $tmp2 > $daily ) return FALSE;

    return TRUE;
  }

  public static function text_is_valid($text)
  {
    if( $text == '' ) return FALSE;
    if( strlen($text) > 160 ) return FALSE;
    if( preg_match(
		  '~[^\w\s@£$¥èéùìòÇ\fØø\nÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./\:;<=>\?¡ÄÖÑÜ§¿äöñüà\^\{\}\[\]\~\|€]~',
		  $text) ) return FALSE;
    return TRUE;
  }
} // end of class

#
# EOF
#
?>
