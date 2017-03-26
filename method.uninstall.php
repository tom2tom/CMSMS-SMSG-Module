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

$sqlarray = $dict->DropTableSQL($pref.'module_smsg_nums');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($pref.'module_smsg_gates');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($pref.'module_smsg_props');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($pref.'module_smsg_sent');
$dict->ExecuteSQLArray($sqlarray);

$db->DropSequence($pref.'module_smsg_gates_seq');

if($this->before20) {
	$this->DeleteTemplate();
} else {
	try {
		$types = CmsLayoutTemplateType::load_all_by_originator($this->GetName());
		if ($types) {
			foreach ($types as $type) {
				$templates = $type->get_template_list();
				if ($templates) {
					foreach($templates as $tpl) {
						$tpl->delete();
					}
				}
				$type->delete();
			}
		}
	} catch (Exception $e) {}
}

$this->RemovePreference();

$this->RemovePermission('AdministerSMSGateways');
$this->RemovePermission('ModifySMSGateways');
$this->RemovePermission('ModifySMSGateTemplates');

$this->RemoveEvent($this->GetName(),'SMSDeliveryReported');

?>
