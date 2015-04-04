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

$db = $this->GetDb();
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
mobile C(25),
ip C(25),
msg C(160),
sdate ".CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_sent',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

/**
@apiconvert: enum for data conversion prior to transmission
as-is = SMSG::DATA_ASIS
rawurlencode = SMSG::DATA_RAWURL
urlencode = SMSG::DATA_URL
htmlentities = 4
htmlspecialchars = 8
for password parameter, +SMSG::DATA_PW
*/
$flds = "
gate_id	I KEY,
alias C(48),
title C(128) NOTNULL,
description C(255),
apiconvert I(1) NOTNULL DEFAULT 0,
enabled I(1) NOTNULL DEFAULT 1,
active I(1) NOTNULL DEFAULT 0
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_gates',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$db->CreateSequence($pref.'module_smsg_gates_seq');
/**
@apiconvert: enum for data conversion prior to transmission, OR'd with
 corresponding field from gates table
as-is = SMSG::DATA_ASIS
rawurlencode = SMSG::DATA_RAWURL
urlencode = SMSG::DATA_URL
htmlentities = 4
htmlspecialchars = 8
for password parameter, +SMSG::DATA_PW
*/
$flds = "
gate_id I NOTNULL,
title C(128),
value C(255),
apiname C(64),
apiconvert I(1) DEFAULT 0,
apiorder I(1) DEFAULT -1,
active I(1) DEFAULT 1
";
$sqlarray = $dict->CreateTableSQL($pref.'module_smsg_props',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

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

//$this->CreateEvent('X');
//$this->CreateEvent('Y');
//$this->AddEventHandler('SMSG','?');
//$this->AddEventHandler('Core','?');

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('installed',$this->GetVersion()));
#
# EOF
#
?>
