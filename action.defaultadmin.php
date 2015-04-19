<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

/**
 @module: the module that is displaying the template
 @smarty: the current smarty object
 @modify: boolean, whether to setup for full editing
 @dflttpl: boolean, whether to setup for editing default template
 @id: instance id of @module
 @returnid: page id to use on subsequent forms and links
 @activetab: tab to return to
 @prefix: template full-name prefix ('enternumber_' or 'entertext_')
 @prefdefname: name of preference that contains the base-name of the current
  default template for @prefix
 */
function SetupTemplateList(&$module,&$smarty,$modify,$dflttpl,
	$id,$returnid,$activetab,
	$prefix,$prefdefname
	)
{
	if( $modify )
	{
		$theme = cmsms()->variables['admintheme'];
		$trueicon = $theme->DisplayImage('icons/system/true.gif',$module->Lang('default_tip'),'','','systemicon');
		$falseicon = $theme->DisplayImage('icons/system/false.gif',$module->Lang('defaultset_tip'),'','','systemicon');
		$editicon = $theme->DisplayImage('icons/system/edit.gif',$module->Lang('edit_tip'),'','','systemicon');
		$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$module->Lang('deleteone_tip'),'','','systemicon');
		$prompt = $module->Lang('sure_ask');
		$args = array('prefix'=>$prefix,'activetab'=>$activetab);
	}
	else
		$yes = $module->Lang('yes');

	$defaultname = $module->GetPreference($prefdefname);
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
		if( $modify )
		{
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
					$args,'',$prompt);
		}
		else
		{
			$row->name = $one;
			$row->default = ( $default ) ? $yes:'';
			$row->editlink = '';
			$row->deletelink = '';
		}
		$rowarray[] = $row;
	}
	if( $modify && $dflttpl )
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
		 '" class="systemicon" onclick="return confirm(\''.$prompt.'\');" />';
		$args['mode'] = 'revert';
		$row->deletelink = $module->CreateLink($id,'settemplate',$returnid,$reverticon,$args);
		$rowarray[] = $row;
	}

	$smarty->assign($prefix.'items',$rowarray);
	$smarty->assign('parent_module_name',$module->GetFriendlyName());
	$smarty->assign('titlename',$module->Lang('name'));
	$smarty->assign('titledefault',$module->Lang('default'));
	if( $modify )
	{
		$args['mode'] = 'add';
		$add = $module->CreateImageLink($id,'settemplate',$returnid,
		 $module->Lang('add_template'),
		 'icons/system/newobject.gif',
		 $args,'','',FALSE);
	}
	else
		$add = '';
	$smarty->assign('add_'.$prefix.'template',$add);

}

smsg_utils::refresh_gateways($this);
$objs = smsg_utils::get_gateways_full($this);
if( !$objs )
{
	echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
	return;
}

$padm = $this->CheckPermission('AdministerSMSGateways');
$pmod = $padm || $this->CheckPermission('ModifySMSGateways');
$ptpl = $padm || $this->CheckPermission('ModifySMSGateTemplates');
$puse = $this->CheckPermission('UseSMSGateways');

$smarty->assign('padm',$padm);
$smarty->assign('pmod',$pmod);
$smarty->assign('ptpl',$ptpl);
$smarty->assign('puse',$puse);

$headers = $this->StartTabHeaders();
if( $pmod || $puse)
	$headers .=
 $this->SetTabHeader('gates',$this->Lang('gateways')).
 $this->SetTabHeader('test',$this->Lang('test'));
 $this->SetTabHeader('mobiles',$this->Lang('phone_numbers'));
if( $ptpl || $puse )
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

if( $pmod || $puse )
{
	$smarty->assign('tabstart_gates',$this->StartTab('gates',$params));
	$smarty->assign('formstart_gates',$this->CGCreateFormStart($id,'savegates'));
	//construct URL (pretty or not)
	$returnid = cmsms()->GetContentOperations()->GetDefaultContent();
	$smarty->assign('reporturl',$this->CreateLink($id,'devreport',$returnid,'',
		array(),'',TRUE,FALSE,'',FALSE,'SMSG/devreport'));

	if( $pmod )
	{
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
	}
	else
	{
		foreach( $objs as $key=>&$rec )
			$rec = $rec['obj']->get_setup_form();
		unset($rec);
	}
	$smarty->assign('gatesdata',$objs);

	$smarty->assign('tabstart_test',$this->StartTab('test',$params));
	$smarty->assign('formstart_test',$this->CGCreateFormStart($id,'smstest'));
	
	$smarty->assign('tabstart_mobiles',$this->StartTab('mobiles',$params));
	$query = 'SELECT * FROM '.cms_db_prefix().'module_smsg_nums ORDER BY id';
	$data = $db->GetAll($query);
	if( $data )
	{
		$edtip = $this->Lang('edit_tip'); 
		$deltip = $this->Lang('deleteone_tip');
		$prompt = $this->Lang('ask_delete_mobile');
		foreach( $data as &$rec )
		{
			$rec = (object)$rec;
			if( $pmod )
			{
				$rec->editlink = $this->CreateImageLink($id,'edit_mobile','','',
					'icons/system/edit.gif',array('mid'=>$rec->id),'','',
					TRUE,FALSE,'title="'.$edtip.'"');
				$rec->deletelink = $this->CreateImageLink($id,'del_mobile','','',
					'icons/system/delete.gif',array('mid'=>$rec->id),'delitemlink',$prompt,
					TRUE,FALSE,'title="'.$deltip.'"');
			}
		}
		unset($rec);
		$smarty->assign('numbers',$data);
	}
	else
		$smarty->assign('nonumbers',$this->Lang('nonumbers'));
	if( $pmod )
		$smarty->assign('add_mobile',$this->CreateImageLink($id,'edit_mobile','',$this->Lang('add_mobile'),
			'icons/system/newobject.gif',array(),'','',FALSE));
}
if( $ptpl || $puse )
{
	$tid = 'enternumber';
	$smarty->assign('tabstart_enternumber',$this->StartTab($tid,$params));
	SetupTemplateList($this,$smarty,$ptpl,$padm,
		$id,$returnid,$tid, //tab to come back to
		'enternumber_', //'prefix' of templates' full-name
		SMSG::PREF_ENTERNUMBER_TPLDFLT); //preference holding name of default template

	$tid = 'entertext';
	$smarty->assign('tabstart_entertext',$this->StartTab($tid,$params));
	SetupTemplateList($this,$smarty,$ptpl,$padm,
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
