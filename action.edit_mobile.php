<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if (!($this->CheckPermission('AdministerSMSGateways')
  || $this->CheckPermission('ModifySMSGateways'))) exit;

$mid = '';
$name = '';
$mobile = '';
$pref = cms_db_prefix();

if (isset($params['mid'])) {
    $mid = (int)$params['mid'];
}

if ($mid != '') {
	$sql = 'SELECT * FROM '.$pref.'module_smsg_nums WHERE id=?';
	$tmp = $db->GetRow($sql,[$mid]);
	if (!$tmp) {
		$this->SetError($this->Lang('error_notfound'));
		$this->Redirect($id,'defaultadmin','',['activetab'=>'mobiles']);
	}
	$name = $tmp['name'];
	$mobile = $tmp['mobile'];
}

if (isset($params['cancel'])) {
	$this->Redirect($id,'defaultadmin','',['activetab'=>'mobiles']);
} else if (isset($params['submit'])) {
	$name = trim($params['name']);
	$mobile = trim($params['mobile']);
	$error = '';

	// basic data checks
	if (!$name || !is_numeric($mobile)) {
		$error = $this->Lang('error_invalid_info');
	}

	if (empty($error)) {
		// check for duplicate name
		$sql = 'SELECT id FROM '.$pref.'module_smsg_nums WHERE name=?';
		$parms = [];
		if ($mid != '') {
			$sql .= ' AND id!=?';
			$parms[] = $mid;
		}
		$tmp = $db->GetOne($sql,$parms);
		if ($tmp) {
			$error = $this->Lang('error_name_exists');
		}
	}

	if (empty($error)) {
		// good to go... do add or insert
		if ($mid == '') {
			// insert
			$sql = 'INSERT INTO '.$pref.'module_smsg_nums (name,mobile) VALUES(?,?)';
			$db->Execute($sql,[$name,$mobile]);
			$res = $db->Affected_Rows() > 0;
		} else {
			// update
			$sql = 'UPDATE '.$pref.'module_smsg_nums SET name=?,mobile=? WHERE id=?';
			$db->Execute($sql,[$name,$mobile,$mid]);
			$res = TRUE; //$db->Affected_Rows() unreliable
		}

		if (!$res) {
			$error = $this->Lang('error_db_op_failed');
		}
	}

	if (!empty($error)) {
		$this->SetError($error);
	}
	$this->Redirect($id,'defaultadmin','',['activetab'=>'mobiles']);
}

// build the form
$tplvars = [
	'formstart' => $this->CreateFormStart($id,'edit_mobile',$returnid,'POST','','','',$params),
	'formend' => $this->CreateFormEnd(),
	'name' => $name,
	'mobile' => $mobile
];

echo SMSG\Utils::ProcessTemplate($this,'edit_mobile.tpl',$tplvars);
