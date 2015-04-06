<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$mid = '';
$name = '';
$mobile = '';
$pref = cms_db_prefix();
$this->SetCurrentTab('mobiles');

if( isset($params['mid']) )
  {
    $mid = (int)$params['mid'];
  }

if( $mid != '' )
  {
	$query = 'SELECT * FROM '.$pref.'module_smsg WHERE id=?';
	$tmp = $db->GetRow($query,array($mid));
	if( !$tmp )
	  {
		$this->SetError($this->Lang('error_notfound'));
		$this->RedirectToTab($id);
	  }
	$name = $tmp['name'];
	$mobile = $tmp['mobile'];
  }

if( isset($params['cancel']) )
  {
	$this->RedirectToTab($id);
  }
else if( isset($params['submit']) )
  {
	$name = trim($params['name']);
	$mobile = trim($params['mobile']);
	$error = '';

	// do basic data checks
	if( $name == '' || !is_numeric($mobile) )
	  {
		$error = $this->Lang('error_invalid_info');
	  }

	if( empty($error) )
	  {
		// check for duplicate name
		$query = 'SELECT id FROM '.$pref.'module_smsg WHERE name=?';
		$parms = array();
		if( $mid != '' )
		  {
			$query .= ' AND id != ?';
			$parms[] = $mid;
		  }
		$tmp = $db->GetOne($query,$parms);
		if( $tmp )
		  {
			$error = $this->Lang('error_name_exists');
		  }
	  }

	if( empty($error) )
	{
	// good to go... do add or insert
	$dbr = '';
	if( $mid == '' )
	  {
		// insert
		$query = 'INSERT INTO '.$pref.'module_smsg (name,mobile) VALUES(?,?)';
		$dbr = $db->Execute($query,array($name,$mobile));
	  }
	else
	  {
		// update
		$query = 'UPDATE '.$pref.'module_smsg SET name=?, mobile=? WHERE id=?';
		$dbr = $db->Execute($query,array($name,$mobile,$mid));
	  }

	if( !$dbr )
	  {
		$error = $this->Lang('error_db_op_failed');
	  }
	}

	if( !empty($error) )
	  {
		$this->SetError($error);
	  }
	$this->RedirectToTab($id);
  }

//
// build the form
//
$smarty->assign('formstart',$this->CGCreateFormStart($id,'edit_mobile',$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());
$smarty->assign('name',$name);
$smarty->assign('mobile',$mobile);

echo $this->ProcessTemplate('edit_mobile.tpl');

?>
