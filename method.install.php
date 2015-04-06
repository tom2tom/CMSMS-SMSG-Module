<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
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
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
gate_id	I KEY,
alias C(48),
title C(128) NOTNULL,
description C(255),
enabled I(1) NOTNULL DEFAULT 1,
active I(1) NOTNULL DEFAULT 0
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_gates',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$db->CreateSequence($pref.'module_smsg_gates_seq');

$flds = "
gate_id I NOTNULL,
title C(128),
value C(255),
apiname C(64),
encrypt I(1) DEFAULT 0,
apiorder I(1) DEFAULT -1,
enabled I(1) DEFAULT 1
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

//enter-number templates
$fn = cms_join_path(dirname(__FILE__),'templates','orig_enternumber_template.tpl');
if(is_file($fn))
  {
    $template = file_get_contents($fn);
    $this->SetPreference(SMSG::PREF_NEWENTERNUMBER_TPL,$template);
    $this->SetTemplate('enternumber_Sample',$template);
    $this->SetPreference(SMSG::PREF_DFLTENTERNUMBER_TPL,'Sample');
  }

//enter-text templates
$fn = cms_join_path(dirname(__FILE__),'templates','orig_entertext_template.tpl');
if(is_file($fn))
  {
    $template = file_get_contents($fn);
    $this->SetPreference(SMSG::PREF_NEWENTERTEXT_TPL,$template);
    $this->SetTemplate('entertext_Sample',$template);
    $this->SetPreference(SMSG::PREF_DFLTENTERTEXT_TPL,'Sample');
  }

$this->CreatePermission('AdministerSMSGateways',$this->Lang('perm_admin'));
$this->CreatePermission('ModifySMSGateways',$this->Lang('perm_modify'));
$this->CreatePermission('ModifySMSGateTemplates',$this->Lang('perm_templates'));

?>
