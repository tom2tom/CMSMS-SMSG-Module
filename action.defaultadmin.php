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

cgsms_utils::refresh_gateways();
$objs = cgsms_utils::get_gateways_full();
if( !$objs )
  {
    echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
    return;
  }

$padm = $this->CheckPermission('AdministerSMSGateways');
$pmod = $padm || $this->CheckPermission('ModifySMSGateways');
$ptpl = $padm || $this->CheckPermission('ModifySMSGateTemplates');

$listname = array();
$listnames[-1] = $this->Lang('none');
foreach( $objs as $key => $rec )
  {
    $listnames[$key] = $rec['name'];
  }

echo $this->StartTabHeaders();

if( $pmod )
  {
    echo $this->SetTabHeader('mobiles',$this->Lang('mobile_numbers'));
    echo $this->SetTabHeader('settings',$this->Lang('settings'));
    echo $this->SetTabHeader('security',$this->Lang('security_tab_lbl'));
    echo $this->SetTabHeader('test',$this->Lang('test'));
  }
if( $ptpl )
  {
    echo $this->SetTabHeader('enternumber',$this->Lang('enter_number_templates'));
    echo $this->SetTabHeader('entertext',$this->Lang('enter_text_templates'));
    echo $this->SetTabHeader('dflt_templates',$this->Lang('default_templates'));
  }
echo $this->EndTabHeaders();

echo $this->StartTabContent();

if( $pmod )
  {
    echo $this->StartTab('mobiles',$params);
    include(cms_join_path(dirname(__FILE__),'function.admin_mobiles_tab.php'));
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
    include(cms_join_path(dirname(__FILE__),'function.security_tab.php'));
    echo $this->EndTab();

    echo $this->StartTab('test',$params);
    $smarty->assign('formstart',$this->CGCreateFormStart($id,'admin_smstest'));
    $smarty->assign('formend',$this->CreateFormEnd());
    echo $this->ProcessTemplate('admin_testtab.tpl');
    echo $this->EndTab();
  }
if( $ptpl )
  {
    echo $this->StartTab('enternumber',$params);
    include(cms_join_path(dirname(__FILE__),'function.enternumber_templates_tab.php'));
    echo $this->EndTab();

    echo $this->StartTab('entertext',$params);
    include(cms_join_path(dirname(__FILE__),'function.entertext_templates_tab.php'));
    echo $this->EndTab();

    echo $this->StartTab('dflt_templates',$params);
    include(cms_join_path(dirname(__FILE__),'function.dflt_templates_tab.php'));
    echo $this->EndTab();
  }

echo $this->EndTabContent();

//js to show only the frameset for selected gateway
echo <<<EOS

<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
  $('.sms_gateway_panel').hide();
  var val = $('#sms_gateway').val();
  $('#'+val).show();
  $('#sms_gateway').change(function(){
    $('.sms_gateway_panel').hide();
    var val = $('#sms_gateway').val();
    $('#'+val).show();
  });
});
//]]>
</script>

EOS;
#
# EOF
#
?>
