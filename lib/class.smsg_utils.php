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

class smsg_utils
{
  const MODNAME = 'SMSG';

  public static function get_gateways_full()
  {
	$db = cmsms()->GetDb();
	$aliases = $db->GetCol('SELECT alias FROM '.cms_db_prefix().'module_smsg_gates WHERE enabled<>0');
	if( !$aliases )
		return FALSE;
	$dir = cms_join_path(dirname(__FILE__),'gateways','');
	$module = cge_utils::get_module(self::MODNAME);
	$objs = array();
	foreach( $aliases as $thisone )
	  {
		$classname = $thisone.'_sms_gateway';
		include_once($dir.'class.'.$classname.'.php');
		$obj = new $classname($module);
		//return array, so other keys may be added, upstream
		$objs[$thisone] = array('obj' => $obj);
	  }

	return $objs;
  }

  public static function get_gateway()
  {
	$db = cmsms()->GetDb();
	$alias = $db->GetOne('SELECT alias FROM '.cms_db_prefix().'module_smsg_gates WHERE active<>0 AND enabled<>0');
	if( !$alias ) return FALSE;

	$classname = $alias.'_sms_gateway';
	$fn = cms_join_path(dirname(__FILE__),'gateways','class.'.$classname.'.php');
	require_once($fn);
	$module = cge_utils::get_module(self::MODNAME);
	$obj = new $classname($module);

	if( $obj ) return $obj;
	return FALSE;
  }

  public static function setgate_full($classname,$conversion)
  {
	$fn = cms_join_path($module->GetModulePath(),'lib','gateways','class.'.$classname.'.php');
	if(is_file($fn))
	  {
		include_once($fn);
		$module = cge_utils::get_module(self::MODNAME);
		$obj = new $classname($module);
		if( $obj )
		  return self::setgate($obj,$conversion);
	  }
	return FALSE;
  }

  public static function setgate(&$obj,$conversion)
  {
	$alias = $obj->get_alias();
	if( !$alias ) return FALSE;
	$title = $obj->get_name();
	if( !$title ) return FALSE;
	$desc = $obj->get_description();
	if( !$desc ) $desc = NULL;

	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	//upsert, sort-of
	$sql = 'SELECT gate_id FROM '.$pref.'module_smsg_gates WHERE alias=?';
	$gid = $db->GetOne($sql,array($alias));
	if( !$gid )
	 {
	  $gid = $db->GenID($pref.'module_smsg_gates_seq');
	  $sql = 'INSERT INTO '.$pref.'module_smsg_gates (gate_id,alias,title,description,apiconvert) VALUES (?,?,?,?,?)';
	  $db->Execute($sql,array($gid,$alias,$title,$desc,$conversion));
	 }
	else
	 {
	   $gid = (int)$gid;
	   $sql = 'UPDATE '.$pref.
		'module_smsg_gates set title=?,description=?,apiconvert=? WHERE gate_id=?';
	   $db->Execute($sql,array($title,$desc,$conversion,$gid));
	 }
	return $gid;
  }

  //$props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=apiconvert
  public static function setprops($gid,$props)
  {
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	//upsert, sort-of
	$sql1 = 'UPDATE '.$pref.
	 'module_smsg_props SET title=?,value=?,apiconvert=?,apiorder=? WHERE gate_id=? AND apiname=?';
	$sql2 = 'INSERT INTO '.$pref.
	 'module_smsg_props (gate_id,title,value,apiname,apiconvert,apiorder)
SELECT ?,?,?,?,?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS
(SELECT 1 FROM '.$pref.'module_smsg_props T1 WHERE T1.gate_id=? AND T1.apiname=?)';
	$o = 1;
	foreach($props as &$data)
	  {
		$db->Execute($sql1,array($data[0],$data[2],$data[3],$o,$gid,$data[1]));
		$db->Execute($sql2,array($gid,$data[0],$data[2],$data[1],$data[3],$o,$gid,$data[1]));
		$o++;
	  }
	unset($data);
  }

  public static function getprops($gid)
  {
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$conv = $db->GetOne('SELECT apiconvert FROM '.$pref.
	 'module_smsg_gates WHERE gate_id=? AND enabled<>0 AND active<>0',array($gid));
	if($conv === FALSE)
		return array();
	$conv = (int)$conv;
	$props = $db->GetAssoc('SELECT apiname,value,apiconvert FROM '.$pref.
	 'module_smsg_props WHERE gate_id=? AND active<>0 ORDER BY apiorder',
	 array($gid));
	foreach($props as &$row)
	  {
		if($row['apiconvert'] >= SMSG::DATA_PW)
		  {
			$row['value'] = self::decrypt_value($row['value']);
		  	$row['apiconvert'] -= SMSG::DATA_PW;
		  }
	  	$rowconv = (int)$row['apiconvert'] | $conv;
		switch($rowconv)
		  {
			case SMSG::DATA_RAWURL:
			 	$row = rawurlencode($row['value']);
				break;
			case SMSG::DATA_URL:
			 	$row = urlencode($row['value']);
				break;
			default:
			 	$row = $row['value'];
			 	break;
		  }
	  }
	unset($row);
	$props['apiconvert'] = $conv;
	return $props;
  }

  public static function encrypt_value($value,$passwd = FALSE)
  {
	if( $value )
	  {
		$module = cge_utils::get_module(self::MODNAME);
		if( !$passwd )
		  {
			$passwd = $module->GetPreference('masterpass');
			if( $passwd )
			  {
				$s = base64_decode(substr($passwd,5));
				$passwd = substr($s,5);
			  }
		  }
		if( $passwd && $module->havemcrypt )
		  {
			$e = new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS);
			$value = $e->encrypt($value,$passwd);
		  }
	  }
	return $value;
  }

  public static function decrypt_value($value,$passwd = FALSE)
  {
	if( $value )
	  {
		$module = cge_utils::get_module(self::MODNAME);
		if( !$passwd )
		  {
			$passwd = $module->GetPreference('masterpass');
			if( $passwd )
			  {
				$s = base64_decode(substr($passwd,5));
				$passwd = substr($s,5);
			  }
		  }
		if( $passwd && $module->havemcrypt )
		  {
			$e = new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS);
			$value = $e->decrypt($value,$passwd);
		  }
	  }
	return $value;
  }

  public static function refresh_gateways()
  {
	$dir = cms_join_path(dirname(__FILE__),'gateways','');
	$files = glob($dir.'class.*sms_gateway.php');
	if( !$files )
		 return;

	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$query = 'SELECT gate_id FROM '.$pref.'module_smsg_gates WHERE alias=?';
	$module = cge_utils::get_module(self::MODNAME);
	$found = array();
	foreach( $files as &$thisfile )
	  {
		include($thisfile);
		$classname = str_replace(array($dir,'class.','.php'),array('','',''),$thisfile);
		$obj = new $classname($module);
		$alias = $obj->get_alias();
		$res = $db->GetOne($query,array($alias));
		if( !$res )
			$res = $obj->upsert_tables();
		$found[] = $res;
	  }
	unset($thisfile);

	$fillers = implode(',',$found);
	$query = 'DELETE FROM '.$pref.'module_smsg_gates WHERE gate_id NOT IN ('.$fillers.')';
	$db->Execute($query);
	$query = 'DELETE FROM '.$pref.'module_smsg_props WHERE gate_id NOT IN ('.$fillers.')';
	$db->Execute($query);
  }

  public static function get_msg(&$gateway,$num,$stat,$msg,$opt = '')
  {
	$module = cge_utils::get_module(self::MODNAME);
	$ip = getenv('REMOTE_ADDR');
	$txt = '';
	if( $stat == smsg_sender_base::STAT_OK )
	  {
		$txt .= $module->Lang($stat,$msg,$num,$ip,$gateway->get_smsid()); //CHECKME
	  }
	else if( $stat == smsg_sender_base::STAT_ERROR_OTHER )
	  {
		$txt .= $module->Lang($stat,$opt,$msg,$num,$ip,$gateway->get_smsid()); //CHECKME
	  }
	else if( $stat != smsg_sender_base::STAT_NOTSENT )
	  {
		$txt .= $module->Lang($stat,$msg,$num,$ip); //CHECKME
	  }
	return $txt;
  }

  public static function get_delivery_msg(&$gateway,$stat,$smsid,$smsto)
  {
	$module = cge_utils::get_module(self::MODNAME);
	$ip = getenv('REMOTE_ADDR');
	return ''.$module->Lang($stat,$smsid,$smsto,$ip); //CHECKME
  }

  public static function get_reporting_url()
  {
	// get the default page id
	$contentops = cmsms()->GetContentOperations();
	$returnid = $contentops->GetDefaultContent();
	$module = cge_utils::get_module(self::MODNAME);

	$prettyurl = 'SMSG/devreport';
	$url = $module->CreateURL('cntnt01','devreport',$returnid,array(),false,$prettyurl);
	return $url;
  }

  public static function is_valid_phone($number)
  {
	$formats = array(
	 '+##########',
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
	$db = cmsms()->GetDb();
	$query = 'INSERT INTO '.cms_db_prefix().
	 'module_smsg_sent (mobile,ip,msg,sdate) VALUES (?,?,?,NOW())';
	$db->Execute($query,array($mobile,$ip_address,$msg));
  }

  public static function ip_can_send($ip_address)
  {
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$t = time();
	$now = $db->DbTimeStamp($t);
	$date1 = $db->DbTimeStamp($t-3600);
	$date2 = $db->DbTimeStamp($t-24*3600);
	$query = 'SELECT COUNT(mobile) AS num FROM '.$pref.
	 "module_smsg_sent WHERE ip=? AND (sdate BETWEEN $date1 and $now)";
	$num = $db->GetOne($query,array($ip_address));

	$module = cge_utils::get_module(self::MODNAME);
	$hourly = $module->GetPreference('hourlimit');
	if( $num > $hourly ) return FALSE;

	$query = 'SELECT COUNT(mobile) AS num FROM '.$pref.
	 "module_smsg_sent WHERE ip=? AND (sdate BETWEEN $date2 and $now)";
	$num = $db->GetOne($query,array($ip_address));
	$daily = $module->GetPreference('daylimit');
	if( $num > $daily ) return FALSE;

	return TRUE;
  }

  public static function text_is_valid($text,$len = 160)
  {
	if( $text == '' ) return FALSE;
	if( $len && strlen($text) > $len ) return FALSE;
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
