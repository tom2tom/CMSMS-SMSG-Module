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

$objs = cgsms_utils::get_gateways_full();
if( !is_array($objs) || count($objs) == 0 )
  {
    echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
    return;
  }

$listname = array();
$listnames[-1] = $this->Lang('none');
foreach( $objs as $key => $rec )
  {
    $listnames[$key] = $rec['name'];
  }

echo $this->StartTabHeaders();
if( $this->CheckPermission('Modify Site Preferences') )
  {
    echo $this->SetTabHeader('mobiles',$this->Lang('mobile_numbers'));
    echo $this->SetTabHeader('settings',$this->Lang('settings'));
    echo $this->SetTabHeader('security',$this->Lang('security_tab_lbl'));
    echo $this->SetTabHeader('test',$this->Lang('test'));
  }
if( $this->CheckPermission('Modify Templates') )
  {
    echo $this->SetTabHeader('enternumber',$this->Lang('enter_number_templates'));
    echo $this->SetTabHeader('entertext',$this->Lang('enter_text_templates'));
    echo $this->SetTabHeader('dflt_templates',$this->Lang('default_templates'));
  }
echo $this->EndTabHeaders();





echo $this->StartTabContent();

if( $this->CheckPermission('Modify Site Preferences') )
  {
    echo $this->StartTab('mobiles',$params);
    include(dirname(__FILE__).'/function.admin_mobiles_tab.php');
    echo $this->EndTab();
    
    echo $this->StartTab('settings',$params);
    $smarty->assign('formstart',$this->CGCreateFormStart($id,'admin_savesettings'));
    $smarty->assign('formend',$this->CreateFormEnd());
    $smarty->assign('reporturl',cgsms_utils::get_reporting_url());
    $smarty->assign('gatewaynames',$listnames);
    $smarty->assign('sms_gateway',$this->GetPreference('sms_gateway','-1'));
    $smarty->assign('objects',$objs);
    echo $this->ProcessTemplate('admin_settingstab.tpl');
    echo $this->EndTab();

    echo $this->StartTab('security',$params);
    include(dirname(__FILE__).'/function.security_tab.php');
    echo $this->EndTab();

    echo $this->StartTab('test',$params);
    $smarty->assign('formstart',$this->CGCreateFormStart($id,'admin_smstest'));
    $smarty->assign('formend',$this->CreateFormEnd());
    echo $this->ProcessTemplate('admin_testtab.tpl');
    echo $this->EndTab();
  }
if( $this->CheckPermission('Modify Templates') )
  {
    echo $this->StartTab('enternumber',$params);
    include(dirname(__FILE__).'/function.enternumber_templates_tab.php');
    echo $this->EndTab();

    echo $this->StartTab('entertext',$params);
    include(dirname(__FILE__).'/function.entertext_templates_tab.php');
    echo $this->EndTab();

    echo $this->StartTab('dflt_templates',$params);
    include(dirname(__FILE__).'/function.dflt_templates_tab.php');
    echo $this->EndTab();
  }

echo $this->EndTabContent();

#
# EOF
#
?>