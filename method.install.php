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
$sqlarray = $dict->CreateTableSQL($pref.'module_cgsms',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
mobile C(25),
ip C(25),
msg C(160),
sdate ".CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL($pref.'module_cgsms_sent',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

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

//enter-number templates
$fn = cms_join_path(dirname(__FILE__),'templates','orig_enternumber_template.tpl');
if(is_file($fn))
  {
    $template = file_get_contents($fn);
    $this->SetPreference(CGSMS::PREF_NEWENTERNUMBER_TPL,$template);
    $this->SetTemplate('enternumber_Sample',$template);
    $this->SetPreference(CGSMS::PREF_DFLTENTERNUMBER_TPL,'Sample');
  }

//enter-text templates
$fn = cms_join_path(dirname(__FILE__),'templates','orig_entertext_template.tpl');
if(is_file($fn))
  {
    $template = file_get_contents($fn);
    $this->SetPreference(CGSMS::PREF_NEWENTERTEXT_TPL,$template);
    $this->SetTemplate('entertext_Sample',$template);
    $this->SetPreference(CGSMS::PREF_DFLTENTERTEXT_TPL,'Sample');
  }

$this->CreatePermission('ModifySMSGateways',$this->Lang('perm_modgates'));
$this->CreatePermission('ModifySMSGatewayTemplates',$this->Lang('perm_modgatetemplates'));

//$this->CreateEvent('X');
//$this->CreateEvent('Y');
//$this->AddEventHandler('CGSMS','?');
//$this->AddEventHandler('Core','?');

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('installed',$this->GetVersion()));
#
# EOF
#
?>
