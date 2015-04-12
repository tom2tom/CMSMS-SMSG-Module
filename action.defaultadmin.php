<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

/**
 @module: the module that is displaying the template
 @cge: @module 's parent (CGExtensions) module 
 @smarty: the current smarty object
 @padm: boolean, whether the current user has admin authority
 @id: instance id of @module
 @returnid: page id to use on subsequent forms and links
 @activetab: tab to return to
 @prefix: template full-name prefix ('enternumber_' or 'entertext_')
 @prefdefname: name of preference that contains the base-name of the current
  default template for @prefix
 */
function SetupTemplateList(&$module,&$cge,&$smarty,$padm,
	$id,$returnid,$activetab,
	$prefix,$prefdefname
	)
{
	$theme = cmsms()->variables['admintheme'];
	$trueicon = $theme->DisplayImage('icons/system/true.gif',$module->Lang('default_tip'),'','','systemicon');
	$falseicon = $theme->DisplayImage('icons/system/false.gif',$module->Lang('defaultset_tip'),'','','systemicon');
	$editicon = $theme->DisplayImage('icons/system/edit.gif',$module->Lang('edit_tip'),'','','systemicon');
	$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$module->Lang('deleteone_tip'),'','','systemicon');

	$defaultname = $module->GetPreference($prefdefname);
	$args = array('prefix'=>$prefix,'activetab'=>$activetab);
	$rowarray = array();

	$mytemplates = $module->ListTemplates(SMSG::MODNAME);
	array_walk($mytemplates,
		function(&$n,$k,$p){$l=strlen($p);
$n=(strncmp($n,$p,$l) === 0)?substr($n,$l):FALSE;if($n=='defaultcontent')$n=FALSE;
},$prefix);
	$mytemplates = array_filter($mytemplates);
	sort($mytemplates,SORT_LOCALE_STRING);

	foreach( $mytemplates as $one )
	{
		$default = ($one == $defaultname);
		$row = new StdClass();
		$args['template'] = $one;
		$args['mode'] = 'edit';
		$row->name = $module->CreateLink($id,'settemplate',$returnid,$one,$args);
		$row->editlink = $module->CreateLink($id,'settemplate',$returnid,$editicon,$args);

		$args['mode'] = 'default';
		$row->default = ( $default ) ?
			$trueicon:
			$module->CreateLink($id,'settemplate',$returnid,$falseicon,$args);

		$args['mode'] = 'delete';
		$row->deletelink = ( $default ) ?
			'':
			$module->CreateImageLink($id,'settemplate',$returnid,
				$module->Lang('deleteone_tip'),
				'icons/system/delete.gif',
				$args,
				'',
				$cge->Lang('areyousure')
				);
		$rowarray[] = $row;
	}
	if( $padm )
	{
		$row = new StdClass();
		$args['template'] = 'defaultcontent';
		$args['mode'] = 'edit';
		$row->name = $module->CreateLink($id,'settemplate',$returnid,
			'<em>'.$module->Lang('default_template_title').'</em>',$args);
		$row->editlink = $module->CreateLink($id,'settemplate',$returnid,$editicon,$args);

		$row->default = '';

		$reverticon = '<img src="'.$module->GetModuleURLPath().'/images/revert.gif" alt="'.
		 $module->Lang('reset').'" title="'.$module->Lang('reset_tip').
		 '" class="systemicon" onclick="return confirm(\''.$cge->Lang('areyousure').'\');" />';
		$args['mode'] = 'revert';
		$row->deletelink = $module->CreateLink($id,'settemplate',$returnid,$reverticon,$args);
		$rowarray[] = $row;
	}

	$smarty->assign($prefix.'items',$rowarray);
	$smarty->assign('parent_module_name',$module->GetFriendlyName());
	$smarty->assign('nameprompt',$cge->Lang('prompt_name'));
	$smarty->assign('defaultprompt',$cge->Lang('prompt_default'));
	if( $padm )
	{
		$args['mode'] = 'add';
		$add = $module->CreateImageLink($id,'settemplate',$returnid,
		 $cge->Lang('prompt_newtemplate'),
		 'icons/system/newobject.gif',
		 $args,'','',FALSE);
		$smarty->assign('add_'.$prefix.'template',$add);
	}
}

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
if( $ptpl )
{
	$cge = $this->GetModuleInstance('CGExtensions');
	$tid = 'enternumber';
	$smarty->assign('tabstart_enternumber',$this->StartTab($tid,$params));
	SetupTemplateList($this,$cge,$smarty,$padm,
		$id,$returnid,$tid, //tab to come back to
		'enternumber_', //'prefix' of templates' full-name
		SMSG::PREF_ENTERNUMBER_TPLDFLT); //preference holding name of default template
	$tid = 'entertext';
	$smarty->assign('tabstart_entertext',$this->StartTab($tid,$params));
	SetupTemplateList($this,$cge,$smarty,$padm,
		$id,$returnid,$tid,'entertext_',SMSG::PREF_ENTERTEXT_TPLDFLT);
}
if( $padm )
{
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
