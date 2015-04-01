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

	//TODO populate gateways & properties tables for current gateways

	$this->CreatePermission('ModifySMSGateways',$this->Lang('perm_modgates'));
	$this->CreatePermission('ModifySMSGatewayTemplates',$this->Lang('perm_modgatetemplates'));
}

$this->Audit(0, $this->Lang('fullname'), $this->Lang('upgraded',$newversion));

?>
