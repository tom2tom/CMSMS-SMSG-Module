<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
namespace SMSG;

class Utils
{
	public static function get_gateways_full(&$mod = NULL)
	{
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$aliases = $db->GetCol('SELECT alias FROM '.$pref.'module_smsg_gates WHERE enabled>0');
		if (!$aliases) {
			return FALSE;
		}
		$bp = \cms_join_path(__DIR__,'gateways','');
		if ($mod === NULL) {
			$mod = \cms_utils::get_module(\SMSG::MODNAME);
		}
		$objs = [];
		foreach ($aliases as $one) {
			$classname = $one.'_sms_gateway';
			$spaced = 'SMSG\\gateways\\'.$classname;
			if (!class_exists($spaced)) {
				include $bp.'class.'.$classname.'.php';
			}
			$obj = new $spaced($mod);
			//return array, so other keys may be added, upstream
			$objs[$one] = ['obj' => $obj];
		}
		return $objs;
	}

	public static function get_gateway($title = FALSE, &$mod = NULL)
	{
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$alias = ($title) ?
			$db->GetOne('SELECT alias FROM '.$pref.'module_smsg_gates WHERE title=? AND enabled>0',[$title]):
			$db->GetOne('SELECT alias FROM '.$pref.'module_smsg_gates WHERE active>0 AND enabled>0');
		if ($alias) {
			$classname = $alias.'_sms_gateway';
			$spaced = 'SMSG\\gateways\\'.$classname;
			if (!class_exists($spaced)) {
				$fn = \cms_join_path(__DIR__,'gateways','class.'.$classname.'.php');
				require $fn;
			}
			if ($mod === NULL) {
				$mod = \cms_utils::get_module(\SMSG::MODNAME);
			}
			$obj = new $spaced($mod);
			if ($obj) {
				return $obj;
			}
		}
		return FALSE;
	}

	public static function setgate_full(&$mod,$classname)
	{
		$spaced = 'SMSG\\gateways\\'.$classname;
		if (!class_exists($spaced)) {
			$fn = \cms_join_path(__DIR__,'gateways','class.'.$classname.'.php');
			if (is_file($fn)) {
				include $fn;
			} else {
				return FALSE;
			}
		}
		$obj = new $spaced($mod);
		if ($obj) {
			return self::setgate($obj);
		}
		return FALSE;
	}

	public static function setgate(&$obj)
	{
		$alias = $obj->get_alias();
		if (!$alias) return FALSE;
		$title = $obj->get_name();
		if (!$title) return FALSE;
		$desc = $obj->get_description();
		if (!$desc) $desc = NULL;

		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		//upsert, sort-of
		$sql = 'SELECT gate_id FROM '.$pref.'module_smsg_gates WHERE alias=?';
		$gid = $db->GetOne($sql,[$alias]);
		if (!$gid) {
		  $gid = $db->GenID($pref.'module_smsg_gates_seq');
		  $sql = 'INSERT INTO '.$pref.'module_smsg_gates (gate_id,alias,title,description) VALUES (?,?,?,?)';
		  $db->Execute($sql,[$gid,$alias,$title,$desc]);
		} else {
		   $gid = (int)$gid;
		   $sql = 'UPDATE '.$pref.
			'module_smsg_gates set title=?,description=? WHERE gate_id=?';
		   $db->Execute($sql,[$title,$desc,$gid]);
		}
		return $gid;
	}

	public static function refresh_gateways(&$mod)
	{
		$bp = \cms_join_path(__DIR__,'gateways','');
		$files = glob($bp.'class.*sms_gateway.php');
		if (!$files) {
			 return;
		}
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$sql = 'SELECT gate_id FROM '.$pref.'module_smsg_gates WHERE alias=?';
		$found = [];
		foreach ($files as &$one) {
			include_once $one;
			$classname = str_replace([$bp,'class.','.php'],['','',''],$one);
			$space = 'SMSG\\gateways\\'.$classname;
			$obj = new $space($mod);
			$alias = $obj->get_alias();
			$res = $db->GetOne($sql,[$alias]);
			if (!$res) {
				$res = $obj->upsert_tables();
			}
			$found[] = $res;
		}
		unset($one);

		$fillers = implode(',',$found);
		$sql = 'DELETE FROM '.$pref.'module_smsg_gates WHERE gate_id NOT IN ('.$fillers.')';
		$db->Execute($sql);
		$sql = 'DELETE FROM '.$pref.'module_smsg_props WHERE gate_id NOT IN ('.$fillers.')';
		$db->Execute($sql);
	}

	//$props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
	public static function setprops($gid,$props)
	{
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
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
		foreach($props as &$data) {
			if ($data[3]) {
				$a1 = [$data[0],NULL,$data[2],$data[1],1,$o,$gid,$data[1]];
				$a2 = [$gid,$data[0],NULL,$data[2],$data[1],$data[1],1,$o,$gid,$data[1]];
			} else {
				$a1 = [$data[0],$data[2],NULL,$data[1],0,$o,$gid,$data[1]];
				$a2 = [$gid,$data[0],$data[2],NULL,$data[1],$data[1],0,$o,$gid,$data[1]];
			}
			$db->Execute($sql1,$a1);
			$db->Execute($sql2,$a2);
			++$o;
		}
		unset($data);
	}

	/**
	  Returns array, each key = signature-field value, each value = array
	   with keys 'apiname' and 'value' (for which the actual value is decrypted if relevant)
	  */
	public static function getprops(&$mod,$gid)
	{
		$cfuncs = new Crypter($mod);
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$props = $db->GetAssoc('SELECT signature,apiname,value,encvalue,encrypt FROM '.$pref.
		 'module_smsg_props WHERE gate_id=? AND enabled>0 ORDER BY apiorder',
		 [$gid]);
		foreach ($props as &$row) {
			if ($row['encrypt']) {
				$row['value'] = $cfuncs->decrypt_value($row['encvalue']);
			}
			unset($row['encrypt']);
			unset($row['encvalue']);
		}
		unset($row);
		return $props;
	}

	/**
	get_reporturl:
	@mod: reference to current SMSG module object
	Returns: string, delivery-reports URL
	*/
	public static function get_reporturl(&$mod)
	{
		//construct frontend-url (so no admin login is needed)
		//cmsms 1.10+ also has ->create_url();
		$url = $mod->CreateLink('_','devreport',1,'',[],'',TRUE);
		//strip the fake returnid, so that the default will be used
		$sep = strpos($url,'&amp;');
		return substr($url,0,$sep);
	}

	/*
	This is a varargs function, 2nd argument (if it exists) is either a
	Lang key or one of the SMSG\base_sms_gateway::STAT_* constants
	*/
	public static function get_msg(&$mod)
	{
		$ip = getenv('REMOTE_ADDR');
		if (func_num_args() > 1) {
			$parms = array_slice(func_get_args(),1);
			$txt = $mod->Lang($parms[0],array_slice($parms,1));
			if (strpos($txt,'Missing Languagestring') === FALSE) { //hardcoded in core
				$txt = implode(',',$parms);
				if ($ip && $parms[0] != base_sms_gateway::STAT_NOTSENT) {
					$txt .= ','.$ip;
				}
			} elseif ($ip) {
				if ($txt) $txt .= ',';
				$txt .= $ip;
			}
			return $txt;
		}
		return $ip;
	}

	/*
	This is a varargs function, 2nd argument (if it exists) may be a Lang key
	*/
	public static function get_delivery_msg(&$mod)
	{
		$ip = getenv('REMOTE_ADDR');
		if (func_num_args() > 1) {
			$parms = array_slice(func_get_args(),1);
			$txt = $mod->Lang($parms[0],array_slice($parms,1));
			if (strpos($txt,'Missing Languagestring') === FALSE) { //hardcoded in core
				$txt = implode(',',$parms);
			}
			if ($ip) {
				if ($txt) $txt .= ',';
				$txt .= $ip;
			}
			return $txt;
		}
		return $ip;
	}

	public static function is_valid_phone($number)
	{
		if ($number) {
			$formats = [
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
			 '###########'];

			$str = preg_replace('/[0-9]/','#',$number);
			if (in_array($str,$formats)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public static function log_send($ip_address,$mobile,$msg,$statusmsg = '')
	{
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$sql = 'INSERT INTO '.$pref.'module_smsg_sent (mobile,ip,msg,sdate) VALUES (?,?,?,NOW())';
		$db->Execute($sql,[$mobile,$ip_address,$msg]);
	}

	public static function clean_log(&$mod = NULL,$time = 0)
	{
		if (!$time) {
			$time = time();
		}
		if ($mod === NULL) {
			$mod = \cms_utils::get_module(\SMSG::MODNAME);
		}
		$days = $mod->GetPreference('logdays',1);
		if ($days < 1) {
			$days = 1;
		}
		$time -= $days*86400;
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		if ($mod->GetPreference('logsends')) {
			$limit = $db->DbTimeStamp($time);
			$db->Execute('DELETE FROM '.$pref.'module_smsg_sent WHERE sdate<'.$limit);
		}
		$db->Execute('DELETE FROM '.$pref.'adminlog WHERE timestamp<? AND (item_id='.\SMSG::AUDIT_SEND.
		' OR item_id = '.\SMSG::AUDIT_DELIV.') AND item_name='.\SMSG::MODNAME,[$time]);
	}

	public static function ip_can_send(&$mod,$ip_address)
	{
		$db = \cmsms()->GetDb();
		$pref = \cms_db_prefix();
		$t = time();
		$now = $db->DbTimeStamp($t);

		$limit = $mod->GetPreference('hourlimit',0);
		if ($limit > 0) {
			$date = $db->DbTimeStamp($t-3600);
			$sql = 'SELECT COUNT(mobile) AS num FROM '.$pref.
			 "module_smsg_sent WHERE ip=? AND (sdate BETWEEN $date and $now)";
			$num = $db->GetOne($sql,[$ip_address]);
			if ($num > $limit) {
				return FALSE;
			}
		}
		$limit = $mod->GetPreference('daylimit',0);
		if ($limit > 0) {
			$date = $db->DbTimeStamp($t-24*3600);
			$sql = 'SELECT COUNT(mobile) AS num FROM '.$pref.
			 "module_smsg_sent WHERE ip=? AND (sdate BETWEEN $date and $now)";
			$num = $db->GetOne($sql,[$ip_address]);
			if ($num > $limit) {
				return FALSE;
			}
		}
		return TRUE;
	}

	public static function text_is_valid($text,$len = 160)
	{
		if ($text == '') { return FALSE; }
		if ($len  > 0 && strlen($text) > $len) { return FALSE; }
		if (preg_match(
		  '~[^\w\s@£$¥èéùìòÇ\fØø\nÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./\:;<=>\?¡ÄÖÑÜ§¿äöñüà\^\{\}\[\]\~\|€]~',
		  $text)) { return FALSE; }
		return TRUE;
	}

    /**
	implode_with_key:
    Implode @assoc into a string suitable for forming a URL string with multiple key/value pairs
    @assoc associative array, keys = parameter name, values = corresponding parameter values
    @inglue optional string, inner glue, default '='
    @outglue optional string, outer glue, default '&'
    Returns: string
    */
    public static function implode_with_key($assoc,$inglue='=',$outglue='&')
    {
        $ret = '';
        foreach ($assoc as $tk => $tv) {
			$ret .= $outglue.$tk.$inglue.$tv;
		}
        return substr($ret,strlen($outglue));
    }

	/**
	ProcessTemplate:
	@mod: reference to current SMSG module object
	@tplname: template identifier
	@tplvars: associative array of template variables
	@cache: optional boolean, default TRUE
	Returns: string, processed template
	*/
	public static function ProcessTemplate(&$mod,$tplname,$tplvars,$cache=TRUE)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
		}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			return $mod->ProcessTemplate($tplname);
		} else {
			if ($cache) {
				$cache_id = md5('smsg'.$tplname.serialize(array_keys($tplvars)));
				$lang = \CmsNlsOperations::get_current_language();
				$compile_id = md5('smsg'.$tplname.$lang);
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname),$cache_id,$compile_id,$smarty);
				if (!$tpl->isCached()) {
					$tpl->assign($tplvars);
				}
			} else {
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname),NULL,NULL,$smarty,$tplvars);
			}
			return $tpl->fetch();
		}
	}

	/**
	ProcessTemplateFromDatabase:
	@mod: reference to current SMSG module object
	@tplname: template identifier
	@tplvars: associative array of template variables
	@cache: optional boolean, default TRUE
	Returns: nothing
	*/
	public static function ProcessTemplateFromDatabase(&$mod,$tplname,$tplvars,$cache=TRUE)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
	}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			echo $mod->ProcessTemplateFromDatabase($tplname);
		} else {
			if ($cache) {
				$cache_id = md5('smsg'.$tplname.serialize(array_keys($tplvars)));
				$lang = \CmsNlsOperations::get_current_language();
				$compile_id = md5('smsg'.$tplname.$lang);
				$tpl = $smarty->CreateTemplate($mod->GetTemplateResource($tplname),$cache_id,$compile_id,$smarty);
				if (!$tpl->isCached()) {
					$tpl->assign($tplvars);
				}
			} else {
				$tpl = $smarty->CreateTemplate($mod->GetTemplateResource($tplname),NULL,NULL,$smarty,$tplvars);
			}
			$tpl->display();
		}
	}

	public static function MergeJS($jsincs,$jsfuncs,$jsloads,&$merged)
	{
		if (is_array($jsincs)) {
			$all = $jsincs;
		} elseif ($jsincs) {
			$all = [$jsincs];
		} else {
			$all = [];
		}
		if ($jsfuncs || $jsloads) {
			$all[] =<<<EOS
<script type="text/javascript">
//<![CDATA[
EOS;
			if (is_array($jsfuncs)) {
				$all = array_merge($all,$jsfuncs);
			} elseif ($jsfuncs) {
				$all[] = $jsfuncs;
			}
			if ($jsloads) {
				$all[] =<<<EOS
$(document).ready(function() {
EOS;
				if (is_array($jsloads)) {
					$all = array_merge($all,$jsloads);
				} else {
					$all[] = $jsloads;
				}
				$all[] =<<<EOS
});
EOS;
			}
			$all[] =<<<EOS
//]]>
</script>
EOS;
		}
		$merged = implode(PHP_EOL,$all);
	}
}
