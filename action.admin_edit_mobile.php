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

//
// initialize
//
$mid = '';
$name = '';
$mobile = '';
$this->SetCurrentTab('mobiles');

//
// get parameters
//
if( isset($params['mid']) )
  {
    $mid = (int)$params['mid'];
  }

//
// get the data
//
if( $mid != '' )
  {
    $query = 'SELECT * FROM '.cms_db_prefix().'module_cgsms
               WHERE id = ?';
    $tmp = $db->GetRow($query,array($mid));
    if( !$tmp )
      {
	$this->SetError($this->Lang('error_notfound'));
	$this->RedirectToTab($id);
      }
    $name = $tmp['name'];
    $mobile = $tmp['mobile'];
  }

//
// handle form submission
//
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
	$query = 'SELECT id FROM  '.cms_db_prefix().'module_cgsms 
                   WHERE name = ?';
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
	    $query = 'INSERT INTO '.cms_db_prefix().'module_cgsms
                        (name,mobile) VALUES(?,?)';
	    $dbr = $db->Execute($query,array($name,$mobile));
	  }
	else
	  {
	    // update
	    $query = 'UPDATE '.cms_db_prefix().'module_cgsms
                         SET name = ?, mobile = ? 
                       WHERE id = ?';
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
$smarty->assign('formstart',$this->CGCreateFormStart($id,'admin_edit_mobile',$returnid,
						     $params));
$smarty->assign('formend',$this->CreateFormEnd());
$smarty->assign('name',$name);
$smarty->assign('mobile',$mobile);

echo $this->ProcessTemplate('admin_edit_mobile.tpl');
#
# EOF
#
?>