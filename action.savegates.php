<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if (!($this->CheckPermission('AdministerSMSGateways')
  || $this->CheckPermission('ModifySMSGateways'))) exit;

if(isset($params['cancel'])) {
	$this->Redirect($id,'defaultadmin','',['activetab'=>'gates']);
}
$gateway = $params['sms_gateway']; //e.g. 'smsbroadcast' or -1
if (!(isset($params['submit']) || isset($params[$gateway.'~delete']))) {
	$this->Redirect($id,'defaultadmin','',['activetab'=>'gates']);
}
$objs = SMSG\Utils::get_gateways_full($this);
if ($objs) {
	if (isset($params['submit'])) {
		$pref = cms_db_prefix();
		$sql = 'UPDATE '.$pref.'module_smsg_gates SET active=0 WHERE active=1';
		$db->Execute($sql);
		if ($gateway != '-1') {
			$sql = 'UPDATE '.$pref.'module_smsg_gates SET enabled=1,active=1 WHERE alias=?';
			$db->Execute($sql,[$gateway]);
		}
	}
	//property-deletions handled downstream
	foreach ($objs as $classname=>$rec) {
		$rec['obj']->handle_setup_form($params);
	}
}

$this->Redirect($id,'defaultadmin','',['activetab'=>'gates']);
