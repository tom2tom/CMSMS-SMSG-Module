<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class smsg_utils
{
  public static function get_gateways_full(&$module=NULL)
  {
	$db = cmsms()->GetDb();
	$aliases = $db->GetCol('SELECT alias FROM '.cms_db_prefix().'module_smsg_gates WHERE enabled>0');
	if( !$aliases )
		return FALSE;
	$dir = cms_join_path(dirname(__FILE__),'gateways','');
 	if( $module === NULL )
		$module = cge_utils::get_module(SMSG::MODNAME);
	$objs = array();
	foreach( $aliases as $thisone )
	  {
		$classname = $thisone.'_sms_gateway';
		include($dir.'class.'.$classname.'.php');
		$obj = new $classname($module);
		//return array, so other keys may be added, upstream
		$objs[$thisone] = array('obj' => $obj);
	  }

	return $objs;
  }

  public static function get_gateway(&$module=NULL)
  {
	$db = cmsms()->GetDb();
	$alias = $db->GetOne('SELECT alias FROM '.cms_db_prefix().'module_smsg_gates WHERE active>0 AND enabled>0');
	if( !$alias ) return FALSE;

	$classname = $alias.'_sms_gateway';
	$fn = cms_join_path(dirname(__FILE__),'gateways','class.'.$classname.'.php');
	require_once($fn);
 	if( $module === NULL )
		$module = cge_utils::get_module(SMSG::MODNAME);
	$obj = new $classname($module);

	if( $obj ) return $obj;
	return FALSE;
  }

  public static function setgate_full(&$module,$classname)
  {
	$fn = cms_join_path($module->GetModulePath(),'lib','gateways','class.'.$classname.'.php');
	if(is_file($fn))
	  {
		include_once($fn);
		$obj = new $classname($module);
		if( $obj )
		  return self::setgate($obj);
	  }
	return FALSE;
  }

  public static function setgate(&$obj)
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
	  $sql = 'INSERT INTO '.$pref.'module_smsg_gates (gate_id,alias,title,description) VALUES (?,?,?,?)';
	  $db->Execute($sql,array($gid,$alias,$title,$desc));
	 }
	else
	 {
	   $gid = (int)$gid;
	   $sql = 'UPDATE '.$pref.
		'module_smsg_gates set title=?,description=? WHERE gate_id=?';
	   $db->Execute($sql,array($title,$desc,$gid));
	 }
	return $gid;
  }

  public static function refresh_gateways(&$module)
  {
	$dir = cms_join_path(dirname(__FILE__),'gateways','');
	$files = glob($dir.'class.*sms_gateway.php');
	if( !$files )
		 return;

	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$query = 'SELECT gate_id FROM '.$pref.'module_smsg_gates WHERE alias=?';
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

  //$props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
  public static function setprops($gid,$props)
  {
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	//upsert, sort-of
	//NOTE new parameters added with apiname 'todo' & signature NULL
	$sql1 = 'UPDATE '.$pref.
	 'module_smsg_props SET title=?,value=?,encvalue=?,
signature = CASE WHEN signature IS NULL THEN ? ELSE signature END,
encrypt=?,apiorder=? WHERE gate_id=? AND apiname=?';
	$sql2 = 'INSERT INTO '.$pref.
	 'module_smsg_props (gate_id,title,value,encvalue,apiname,signature,encrypt,apiorder)
SELECT ?,?,?,?,?,?,?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS
(SELECT 1 FROM '.$pref.'module_smsg_props T1 WHERE T1.gate_id=? AND T1.apiname=?)';
	$o = 1;
	foreach($props as &$data)
	  {
		if($data[3])
		  {
			$a1 = array($data[0],NULL,$data[2],$data[1],1,$o,$gid,$data[1]);
			$a2 = array($gid,$data[0],NULL,$data[2],$data[1],$data[1],1,$o,$gid,$data[1]);
		  }
		else
		  {
			$a1 = array($data[0],$data[2],NULL,$data[1],0,$o,$gid,$data[1]);
			$a2 = array($gid,$data[0],$data[2],NULL,$data[1],$data[1],0,$o,$gid,$data[1]);
		  }
		$db->Execute($sql1,$a1);
		$db->Execute($sql2,$a2);
		$o++;
	  }
	unset($data);
  }

  /**
  Returns array, each key = signature-field value, each value = array
   with keys 'apiname' and 'value' (for which the actual value is decrypted if relevant)
  */
  public static function getprops(&$module,$gid)
  {
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$props = $db->GetAssoc('SELECT signature,apiname,value,encvalue,encrypt FROM '.$pref.
	 'module_smsg_props WHERE gate_id=? AND enabled>0 ORDER BY apiorder',
	 array($gid));
	foreach($props as &$row)
	  {
		if ($row['encrypt'])
			$row['value'] = self::decrypt_value($module,$row['encvalue']);
		unset($row['encrypt']);
		unset($row['encvalue']);
	  }
	unset($row);
	return $props;
  }

  public static function encrypt_value(&$module,$value,$passwd = FALSE)
  {
	if( $value )
	  {
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

  public static function decrypt_value(&$module,$value,$passwd = FALSE)
  {
	if( $value )
	  {
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

  public static function get_msg(&$module,&$gateway,$num,$stat,$msg,$opt = '')
  {
	$ip = getenv('REMOTE_ADDR');
	$txt = '';
	if( $stat == sms_gateway_base::STAT_OK )
	  {
		$txt .= $module->Lang($stat,$msg,$num,$ip,$gateway->get_smsid()); //CHECKME
	  }
	else if( $stat == sms_gateway_base::STAT_ERROR_OTHER )
	  {
		$txt .= $module->Lang($stat,$opt,$msg,$num,$ip,$gateway->get_smsid()); //CHECKME
	  }
	else if( $stat != sms_gateway_base::STAT_NOTSENT )
	  {
		$txt .= $module->Lang($stat,$msg,$num,$ip); //CHECKME
	  }
	return $txt;
  }

  public static function get_delivery_msg(&$module,&$gateway,$stat,$smsid,$smsto)
  {
	$ip = getenv('REMOTE_ADDR');
	return ''.$module->Lang($stat,$smsid,$smsto,$ip); //CHECKME
  }

  public static function get_reporting_url(&$module)
  {
	// get the default page id
	$contentops = cmsms()->GetContentOperations();
	$returnid = $contentops->GetDefaultContent();

	$prettyurl = 'SMSG/devreport';
	$url = $module->CreateURL('cntnt01','devreport',$returnid,array(),false,$prettyurl);
	return $url;
  }

  public static function is_valid_phone($number)
  {
	if( $number )
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
	  }
	return FALSE;
  }

  public static function log_send($ip_address,$mobile,$msg,$statusmsg = '')
  {
	$db = cmsms()->GetDb();
	$query = 'INSERT INTO '.cms_db_prefix().
	 'module_smsg_sent (mobile,ip,msg,sdate) VALUES (?,?,?,NOW())';
	$db->Execute($query,array($mobile,$ip_address,$msg));
  }

  public static function ip_can_send(&$module,$ip_address)
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

?>
