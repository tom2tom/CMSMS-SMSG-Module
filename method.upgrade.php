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
# This file is distributed as part of an addon module for CMS Made Simple.
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

function addgate($incpath,$classname,&$mod,&$db,$pref)
{
	include ($incpath.'class.'.$classname.'.php');
	$gob = new $classname($mod);
	if($gob)
	{
		$gid = $db->GenID($pref.'module_cgsms_gates_seq');
		$title = $gob->get_name();
		$alias = strtolower(trim(str_replace(array(' ','_','/'),array('','',''),$title)));
		$desc = $gob->get_description();
		if(!$desc) $desc = NULL;
		$sql = 'INSERT INTO '.$pref.'module_cgsms_gates (gate_id,alias,title,description) VALUES (?,?,?,?)';
		$db->Execute($sql,array($gid,$alias,$title,$desc));
		return $gid;
	}
	return FALSE;
}

function addprops($gid,$props,$db,$pref)
{
	$sql = 'INSERT INTO '.$pref.'module_cgsms_props (gate_id,title,value,apiname,apiorder) VALUES(?,?,?,?,?)';
	$o = 1;
	foreach($props as &$data)
	{
		$db->Execute($sql,array($gid,$data[0],$data[2],$data[1],$o));
		$o++;
	}
	unset($data);
}

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$pref = cms_db_prefix();
$taboptarray = array('mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
 'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci');

switch($oldversion)
{
 case "1.0":
	//remove redundant files
	$fp = cms_join_path (dirname(__FILE__),'lib','gateways','class.interlinked_sms_gateway.php');
	if(is_file($fp))
		unlink($fp);
	$fp = cms_join_path (dirname(__FILE__),'templates','interlinked_setup.tpl');
	if(is_file($fp))
		unlink($fp);
 case "1.1":
	$flds = "
gate_id	I KEY,
alias C(48),
title C(128),
description C(255),
active I(1) DEFAULT 1
";
	$sqlarray = $dict->CreateTableSQL($pref.'module_cgsms_gates',$flds,$taboptarray);
	$dict->ExecuteSQLArray($sqlarray);

	$db->CreateSequence($pref.'module_cgsms_gates_seq');
 
	$flds = "
gate_id	I,
title C(128),
value C(255),
apiname C(64),
apiconvert I(1) DEFAULT 0,
apiorder I(1) DEFAULT -1,
active I(1) DEFAULT 1
";
	$sqlarray = $dict->CreateTableSQL($pref.'module_cgsms_props',$flds,$taboptarray);
	$dict->ExecuteSQLArray($sqlarray);

	//populate gateways & properties tables for current gateways
	$incpath = $this->GetModulePath().'/'.'lib'.'/'.'gateways'.'/'; //TODO PATH_SEPARATOR invalid
	$gid = addgate($incpath,'googlevoice_sms_gateway',$this,$db,$pref);
	if($gid)
		addprops($gid,array(
			array($this->Lang('login'),'_email',$this->GetPreference('googlevoice_email')),
			array($this->Lang('password'),'_password',$this->GetPreference('googlevoice_password'))
			),$db,$pref);
	$this->RemovePreference('googlevoice_email');
	$this->RemovePreference('googlevoice_password');
	//-----
	$gid = addgate($incpath,'clickatell_sms_gateway',$this,$db,$pref);
	if($gid)
		addprops($gid,array(
			array($this->Lang('username'),'user',$this->GetPreference('ctell_username')),
			array($this->Lang('password'),'password',$this->GetPreference('ctell_password')),
			array($this->Lang('apiid'),'api_id',$this->GetPreference('ctell_apiid'))
			),$db,$pref);
	$this->RemovePreference('ctell_username');
	$this->RemovePreference('ctell_password');
	$this->RemovePreference('ctell_apiid');
	//-----
	$gid = addgate($incpath,'twilio_sms_gateway',$this,$db,$pref);
	if($gid)
		addprops($gid,array(
			array($this->Lang('account'),'_account',$this->GetPreference('twilio_username')),
			array($this->Lang('token'),'_token',$this->GetPreference('twilio_password')),
			array($this->Lang('from'),'_from',$this->GetPreference('twilio_from'))
			),$db,$pref);
	$this->RemovePreference('twilio_username');
	$this->RemovePreference('twilio_password');
	$this->RemovePreference('twilio_from');
	//-----
	$gid = addgate($incpath,'smsbroadcast_sms_gateway',$this,$db,$pref);
	if($gid)
		addprops($gid,array(
			array($this->Lang('username'),'username',$this->GetPreference('smsbroadcast_username')),
			array($this->Lang('password'),'password',$this->GetPreference('smsbroadcast_password')),
			array($this->Lang('from'),'from',$this->GetPreference('smsbroadcast_from'))
			),$db,$pref);
	$this->RemovePreference('smsbroadcast_username');
	$this->RemovePreference('smsbroadcast_password');
	$this->RemovePreference('smsbroadcast_from');

	$this->CreatePermission('ModifySMSGateways',$this->Lang('perm_modgates'));
	$this->CreatePermission('ModifySMSGatewayTemplates',$this->Lang('perm_modgatetemplates'));
}

$this->Audit(0, $this->Lang('fullname'), $this->Lang('upgraded',$newversion));

?>
