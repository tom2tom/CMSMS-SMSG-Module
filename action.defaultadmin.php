<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

smsg_utils::refresh_gateways($this);
$objs = smsg_utils::get_gateways_full($this);
if( !$objs )
  {
	echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
	return;
  }
//while we're here, do a cleanup
smsg_utils::clean_log($this);

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
	// get list of mobiles
	$query = 'SELECT * FROM '.cms_db_prefix().'module_smsg_nums ORDER BY id';
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

	$smarty->assign('tabstart_settings',$this->StartTab('settings',$params));
	$smarty->assign('formstart_settings',$this->CGCreateFormStart($id,'savesettings'));
	$smarty->assign('reporturl',smsg_utils::get_reporting_url($this));

	$names = array(-1 => $this->Lang('none'));
	foreach( $objs as $key=>&$rec )
	  {
		$names[$key] = $rec['obj']->get_name();
		$rec = $rec['obj']->get_setup_form();
	  }
	unset($rec);
	$current = $db->GetOne('SELECT alias FROM '.cms_db_prefix().
		'module_smsg_gates WHERE enabled=1 AND active=1');
	if( $current == FALSE )
		$current = '-1';

	$smarty->assign('gatecurrent',$current);
	$smarty->assign('gatesnames',$names);
	$smarty->assign('gatesdata',$objs);

	$smarty->assign('tabstart_test',$this->StartTab('test',$params));
	$smarty->assign('formstart_test',$this->CGCreateFormStart($id,'smstest'));
  }
//in the following, we don't use CGExtensions::funcs cuz' they have an unsuitable API
if( $ptpl )
  {
	$smarty->assign('tabstart_enternumber',$this->StartTab('enternumber',$params));
	$smarty->assign('enternumber',
		smsg_utils::ShowTemplateList($this,$id,$returnid,
		'enternumber_', //'prefix' of template preference name
//		SMSG::PREF_NEWENTERNUMBER_TPL,
		'enternumber', //active tab
		SMSG::PREF_ENTERNUMBER_TPLS, //'base' names of all templates (suffix)
		SMSG::PREF_ENTERNUMBER_TPLDFLT, //'base' name of default template
		$this->Lang('title_enternumber_templates'),
		$this->Lang('info_enternumber_templates')));

	$smarty->assign('tabstart_entertext',$this->StartTab('entertext',$params));
	$smarty->assign('entertext',
		smsg_utils::ShowTemplateList($this,$id,$returnid,
		'entertext_',
//		SMSG::PREF_NEWENTERTEXT_TPL,
		'entertext',
		SMSG::PREF_ENTERTEXT_TPLS,
		SMSG::PREF_ENTERTEXT_TPLDFLT,
		$this->Lang('title_entertext_templates'),
		$this->Lang('info_entertext_templates')));
  }
if( $padm)
  {
	$smarty->assign('tabstart_defaults',$this->StartTab('dflt_templates',$params));
	$smarty->assign('defaultnumber',
		smsg_utils::GetDefaultTemplateForm($this,$id,$returnid,
		'enternumber_'.$this->GetPreference(SMSG::PREF_ENTERNUMBER_TPLDFLT),
		$this->Lang('dflt_enternumber_template'),
		'enternumber_template.tpl',
		$this->Lang('info_sysdflt_enternumber_template')));
	$smarty->assign('defaulttext',
		smsg_utils::GetDefaultTemplateForm($this,$id,$returnid,
		'entertext_'.$this->GetPreference(SMSG::PREF_ENTERTEXT_TPLDFLT),
		$this->Lang('dflt_entertext_template'),
		'entertext_template.tpl',
		$this->Lang('info_sysdflt_entertext_template'),
		FALSE,TRUE));
	$smarty->assign('tabstart_security',$this->StartTab('security',$params));
	$smarty->assign('formstart_security',$this->CGCreateFormStart($id,'savesecurity'));
	$smarty->assign('hourlimit',$this->GetPreference('hourlimit'));
	$smarty->assign('daylimit',$this->GetPreference('daylimit'));
	$smarty->assign('logsends',$this->GetPreference('logsends'));
	$smarty->assign('logdays',$this->GetPreference('logdays'));
	$smarty->assign('logdeliveries',$this->GetPreference('logdeliveries'));
	$pw = $this->GetPreference('masterpass');
	if( $pw )
	  {
		$s = base64_decode(substr($pw,5));
		$pw = substr($s,5);
	  }
	$smarty->assign('masterpass',$pw);
  }

echo $this->ProcessTemplate('adminpanel.tpl');

?>
