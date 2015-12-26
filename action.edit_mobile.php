<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if(!($this->CheckPermission('AdministerSMSGateways')
  || $this->CheckPermission('ModifySMSGateways'))) exit;

$mid = '';
$name = '';
$mobile = '';
$pref = cms_db_prefix();

if(isset($params['mid']))
    $mid = (int)$params['mid'];


if($mid != '')
{
	$query = 'SELECT * FROM '.$pref.'module_smsg_nums WHERE id=?';
	$tmp = $db->GetRow($query,array($mid));
	if(!$tmp)
	{
		$this->SetError($this->Lang('error_notfound'));
		$this->Redirect($id,'defaultadmin','',array('activetab'=>'mobiles'));
	}
	$name = $tmp['name'];
	$mobile = $tmp['mobile'];
}

if(isset($params['cancel']))
{
	$this->Redirect($id,'defaultadmin','',array('activetab'=>'mobiles'));
}
else if(isset($params['submit']))
{
	$name = trim($params['name']);
	$mobile = trim($params['mobile']);
	$error = '';

	// basic data checks
	if(!$name || !is_numeric($mobile))
	{
		$error = $this->Lang('error_invalid_info');
	}

	if(empty($error))
	{
		// check for duplicate name
		$query = 'SELECT id FROM '.$pref.'module_smsg_nums WHERE name=?';
		$parms = array();
		if($mid != '')
		{
			$query .= ' AND id!=?';
			$parms[] = $mid;
		}
		$tmp = $db->GetOne($query,$parms);
		if($tmp)
		{
			$error = $this->Lang('error_name_exists');
		}
	}

	if(empty($error))
	{
		// good to go... do add or insert
		$res = '';
		if($mid == '')
		{
			// insert
			$query = 'INSERT INTO '.$pref.'module_smsg_nums (name,mobile) VALUES(?,?)';
			$res = $db->Execute($query,array($name,$mobile));
		}
		else
		{
			// update
			$query = 'UPDATE '.$pref.'module_smsg_nums SET name=?,mobile=? WHERE id=?';
			$res = $db->Execute($query,array($name,$mobile,$mid));
		}

		if(!$res)
		{
			$error = $this->Lang('error_db_op_failed');
		}
	}

	if(!empty($error))
	{
		$this->SetError($error);
	}
	$this->Redirect($id,'defaultadmin','',array('activetab'=>'mobiles'));
}

// build the form
$smarty->assign('formstart',$this->CreateFormStart($id,'edit_mobile',$returnid,'POST','','','',$params));
$smarty->assign('formend',$this->CreateFormEnd());
$smarty->assign('name',$name);
$smarty->assign('mobile',$mobile);

echo $this->ProcessTemplate('edit_mobile.tpl');

?>
