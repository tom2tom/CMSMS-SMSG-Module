<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if (!$this->CheckPermission('AdministerSMSGateways')) exit;

if (isset($params['cancel'])) {
	$this->Redirect($id,'defaultadmin','',['activetab'=>'security']);
}
$this->SetPreference('hourlimit',(int)$params['hourlimit']);
$this->SetPreference('daylimit',(int)$params['daylimit']);
$this->SetPreference('logsends',!empty($params['logsends']));
$this->SetPreference('logdays',(int)$params['logdays']);
$this->SetPreference('logdeliveries',!empty($params['logdeliveries']));
if (isset($params['masterpass'])) {
	$cfuncs = new SMSG\Crypter($this);
	$oldpw = $cfuncs->decrypt_preference('masterpass');
	$newpw = trim($params['masterpass']);
	if ($oldpw != $newpw) {
		//update current passwords
		$pref = cms_db_prefix();
		$sql = 'SELECT gate_id,title,value,encvalue FROM '.$pref.'module_smsg_props WHERE encrypt>0';
		$rows = $db->GetArray($sql);
		if ($rows) {
			if ($newpw) {
				$tofield = 'encvalue';
				$notfield = 'value';
				$encval = 1;
			} else {
				$tofield = 'value';
				$notfield = 'encvalue';
				$encval = 0;
			}
			$sql = 'UPDATE '.$pref.'module_smsg_props SET '.$tofield.'=?,'.$notfield.'=NULL,encrypt=? WHERE gate_id=? AND title=?';
			foreach ($rows as &$onerow) {
				if ($oldpw) {
					$raw = ($onerow['encvalue']) ?
						$cfuncs->decrypt_value($onerow['encvalue'],$oldpw) :
						NULL;
				} else {
					$raw = $onerow['value'];
				}
				if ($newpw) {
					$revised = ($raw) ?
						$cfuncs->encrypt_value($raw,$newpw) :
						NULL;
				} else {
					$revised = $raw;
				}
				if (!$revised) {
					$revised = NULL;
				}
				$db->Execute($sql,[$revised,$encval,$onerow['gate_id'],$onerow['title']]);
			}
			unset($onerow);
		}
		$cfuncs->encrypt_preference('masterpass',$newpw);
	}
}

$this->Redirect($id,'defaultadmin','',['activetab'=>'security']);
