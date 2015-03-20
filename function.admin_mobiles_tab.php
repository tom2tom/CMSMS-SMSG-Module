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

if( !isset($gCms) ) exit;
if( !$this->CheckPermission('Modify Site Preferences') ) return;

// Get the list of mobiles.
$query = 'SELECT * FROM  '.cms_db_prefix().'module_cgsms 
           ORDER BY id ASC';
$tmp = $db->GetArray($query);
if( is_array($tmp) && count($tmp) > 0 )
  {
    for( $i = 0; $i < count($tmp); $i++ )
      {
	$rec =& $tmp[$i];
	$rec['edit_link'] = $this->CreateImageLink($id,'admin_edit_mobile',$returnid,'',
						  'icons/system/edit.gif',
						  array('mid'=>$rec['id']));
	$rec['del_link'] = $this->CreateImageLink($id,'admin_del_mobile',$returnid,'',
						  'icons/system/delete.gif',
						  array('mid'=>$rec['id']), '',
						  $this->Lang('ask_delete_mobile'));
      }

    $smarty->assign('mobiles',$tmp);
  }

$smarty->assign('add_link',
		$this->CreateImageLink($id,'admin_edit_mobile',$returnid,
				       $this->Lang('add_mobile'),
				       'icons/system/newobject.gif',
				       array(),'','',false));

echo $this->ProcessTemplate('admin_mobiles_tab.tpl');
#
# EOF
#
?>
