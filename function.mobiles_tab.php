<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

// get list of mobiles
$query = 'SELECT * FROM  '.cms_db_prefix().'module_smsg ORDER BY id';
$data = $db->GetAll($query);
if( $data )
  {
	$prompt = $this->Lang('ask_delete_mobile');
	foreach( $data as &$rec )
	  {
		$rec['edit_link'] = $this->CreateImageLink($id,'edit_mobile','','','icons/system/edit.gif',array('mid'=>$rec['id']));
		$rec['del_link'] = $this->CreateImageLink($id,'del_mobile','','','icons/system/delete.gif',array('mid'=>$rec['id']),'delitemlink',$prompt);
	  }
	unset( $rec );
  }
$smarty->assign('mobiles',$data);

$smarty->assign('add_mobile',$this->CreateImageLink($id,'edit_mobile','',$this->Lang('add_mobile'),'icons/system/newobject.gif',array(),'','',FALSE));

?>
