<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$db = cmsms()->GetDb();
$dict = NewDataDictionary($db);
$pref = cms_db_prefix();
$taboptarray = array('mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
 'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci');

$flds = "
id I KEY AUTO,
mobile C(25),
name C(25) KEY
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_nums',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
gate_id	I KEY,
alias C(48),
title C(128) NOTNULL,
description C(256),
enabled I(1) NOTNULL DEFAULT 1,
active I(1) NOTNULL DEFAULT 0
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_gates',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$db->CreateSequence($pref.'module_smsg_gates_seq');

//postgres supported pre-1.11
$ftype = (preg_match('/mysql/i',$config['dbms'])) ? 'VARBINARY(256)':'BIT VARYING(2048)';
$flds = "
gate_id I NOTNULL,
title C(96),
value C(256),
encvalue $ftype,
apiname C(64),
signature C(64),
encrypt I(1) DEFAULT 0,
enabled I(1) DEFAULT 1,
apiorder I(1) DEFAULT -1
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_props',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
mobile C(25),
ip C(25),
msg C(160),
sdate ".CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_sent',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$this->SetPreference('hourlimit',5);
$this->SetPreference('daylimit',20);
$this->SetPreference('logsends',TRUE);
$this->SetPreference('logdays',7);
$this->SetPreference('logdeliveries',TRUE);
$this->SetPreference('lastcleared',time());
$this->SetPreference('masterpass','OWFmNT1dGbU5FbnRlciBhdCB5b3VyIG93biByaXNrISBEYW5nZXJvdXMgZGF0YSE=');

$sample = $this->Lang('sample'); //CHECKME Lang not installed yet?
//enter-number template
$fn = cms_join_path(dirname(__FILE__),'templates','enternumber_template.tpl');
$numbertpl = (is_file($fn)) ? ''.@file_get_contents($fn) : FALSE;
//enter-text template
$fn = cms_join_path(dirname(__FILE__),'templates','entertext_template.tpl');
$texttpl = (is_file($fn)) ? ''.@file_get_contents($fn) : FALSE;

if($this->before20)
{
	if($numbertpl)
	{
		$this->SetTemplate(SMSG::PREF_ENTERNUMBER_CONTENTDFLT,$numbertpl); //CHECKME why this too, if sample = default
		$this->SetTemplate('enternumber_'.$sample,$numbertpl);
		$name = $sample;
	}
	else
		$name = '';
	$this->SetPreference(SMSG::PREF_ENTERNUMBER_TPLDFLT,$name); //TODO CHECK uses
	if($texttpl)
	{
		$this->SetTemplate(SMSG::PREF_ENTERTEXT_CONTENTDFLT,$texttpl);
		$this->SetTemplate('entertext_'.$sample,$texttpl);
		$name = $sample;
	}
	else
		$name = '';
	$this->SetPreference(SMSG::PREF_ENTERTEXT_TPLDFLT,$name); //TODO CHECK uses
}
else
{
	$myname = $this->GetName();
	$ttype = new CmsLayoutTemplateType();
	$ttype->set_originator($myname);
	$ttype->set_name('enternumber');
	if($numbertpl)
	{
		$ttype->set_dflt_flag();
		$ttype->set_dflt_contents($numbertpl); //make the sample-template the default
	}
	else
		$ttype->set_dflt_flag(FALSE);
	$ttype->save();

	if($numbertpl)
	{
		$tpl = new CmsLayoutTemplate();
		$tpl->set_type('enternumber');
		$tpl->set_name('enternumber_'.$sample);
		$tpl->set_owner(1); //original admin user OR current installer ?
//		$tpl->set_additional_editors($editors); nobody yet has ModifySMSGateTemplates permission
		$tpl->set_content($numbertpl);
		$tpl->save();
	}

	$ttype = new CmsLayoutTemplateType();
	$ttype->set_originator($myname);
	$ttype->set_name('entertext');
	if($texttpl)
	{
		$ttype->set_dflt_flag();
		$ttype->set_dflt_contents($texttpl);
	}
	else
		$ttype->set_dflt_flag(FALSE);
	$ttype->save();

	if($texttpl)
	{
		$tpl = new CmsLayoutTemplate();
		$tpl->set_type('entertext');
		$tpl->set_name('entertext_'.$sample);
		$tpl->set_owner(1);
//		$tpl->set_additional_editors($editors);
		$tpl->set_content($texttpl);
		$tpl->save();
	}
}

$this->CreatePermission('AdministerSMSGateways',$this->Lang('perm_admin'));
$this->CreatePermission('ModifySMSGateways',$this->Lang('perm_modify'));
$this->CreatePermission('ModifySMSGateTemplates',$this->Lang('perm_templates'));
$this->CreatePermission('UseSMSGateways',$this->Lang('perm_use'));

$this->CreateEvent('SMSDeliveryReported');

?>
