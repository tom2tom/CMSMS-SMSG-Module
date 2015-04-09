<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

smsg_utils::refresh_gateways();
$objs = smsg_utils::get_gateways_full();
if( !$objs )
  {
	echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
	return;
  }
$pref = cms_db_prefix();
//while we're here, do a cleanup
if( $this->GetPreference('logsends') )
  {
	$days = $this->GetPreference('logdays');
	if( !$days ) $days = 1;
	$limit = $db->DbTimeStamp(time()-$days*24*3600);
	$db->Execute('DELETE FROM '.$pref.'module_smsg_sent WHERE sdate<'.$limit);
  }

$padm = $this->CheckPermission('AdministerSMSGateways');
$pmod = $padm || $this->CheckPermission('ModifySMSGateways');
$ptpl = $padm || $this->CheckPermission('ModifySMSGateTemplates');

$smarty->assign('padm',$padm);
$smarty->assign('pmod',$pmod);
$smarty->assign('ptpl',$ptpl);

$headers = $this->StartTabHeaders();
if( $pmod )
	$headers .=
 $this->SetTabHeader('mobiles',$this->Lang('mobile_numbers')).
 $this->SetTabHeader('settings',$this->Lang('gateways')).
 $this->SetTabHeader('test',$this->Lang('test'));
if( $ptpl )
	$headers .=
 $this->SetTabHeader('enternumber',$this->Lang('enter_number_templates')).
 $this->SetTabHeader('entertext',$this->Lang('enter_text_templates'));
if( $padm )
	$headers .=
 $this->SetTabHeader('dflt_templates',$this->Lang('default_templates')).
 $this->SetTabHeader('security',$this->Lang('security_tab_lbl'));
$headers .=
 $this->EndTabHeaders().
 $this->StartTabContent();
$smarty->assign('starttabcontent',$headers);
$smarty->assign('endtab',$this->EndTab());
$smarty->assign('endtabcontent',$this->EndTabContent());
$smarty->assign('formend',$this->CreateFormEnd());

if( $pmod )
  {
	$smarty->assign('tabstart_mobiles',$this->StartTab('mobiles',$params));
	include(cms_join_path(dirname(__FILE__),'function.mobiles_tab.php'));

	$smarty->assign('tabstart_settings',$this->StartTab('settings',$params));
	$smarty->assign('formstart_settings',$this->CGCreateFormStart($id,'savesettings'));
	$smarty->assign('reporturl',smsg_utils::get_reporting_url());

	$names = array(-1 => $this->Lang('none'));
	foreach( $objs as $key=>&$rec )
	  {
		$names[$key] = $rec['obj']->get_name();
		$rec = $rec['obj']->get_setup_form();
	  }
	unset($rec);
	$current = $db->GetOne('SELECT alias FROM '.$pref.'module_smsg_gates WHERE enabled=1 AND active=1');
	if( $current == FALSE )
		$current = '-1';

	$smarty->assign('gatecurrent',$current);
	$smarty->assign('gatesnames',$names);
	$smarty->assign('gatesdata',$objs);

	$smarty->assign('tabstart_test',$this->StartTab('test',$params));
	$smarty->assign('formstart_test',$this->CGCreateFormStart($id,'smstest'));
  }
if( $ptpl )
  {
	$smarty->assign('tabstart_enternumber',$this->StartTab('enternumber',$params));
	include(cms_join_path(dirname(__FILE__),'function.enternumber_templates_tab.php'));

	$smarty->assign('tabstart_entertext',$this->StartTab('entertext',$params));
	include(cms_join_path(dirname(__FILE__),'function.entertext_templates_tab.php'));
  }
if( $padm)
  {
	$smarty->assign('tabstart_defaults',$this->StartTab('dflt_templates',$params));
	include(cms_join_path(dirname(__FILE__),'function.dflt_templates_tab.php'));
  
	$smarty->assign('tabstart_security',$this->StartTab('security',$params));
	$smarty->assign('formstart_security',$this->CGCreateFormStart($id,'savesecurity'));
	include(cms_join_path(dirname(__FILE__),'function.security_tab.php'));
  }

echo $this->ProcessTemplate('adminpanel.tpl');

?>
