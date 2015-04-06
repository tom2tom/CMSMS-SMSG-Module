<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$sql = 'INSERT INTO '.cms_db_prefix().
 'module_smsg_props (gate_id,title,apiname,enabled) VALUES (?,\'---\',\'todo\',0)';
$db->Execute($sql,array((int)$params['gate_id']));

$this->SetCurrentTab('settings');
$this->RedirectToTab($id);

?>
