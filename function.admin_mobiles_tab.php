<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (c) 2010 by Robert Campbell 
#         (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to provide the ability for other
#  modules to send SMS messages
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
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