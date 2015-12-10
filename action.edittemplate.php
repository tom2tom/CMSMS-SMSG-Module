<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright(C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
# action - edittemplate redirected here from action settemplate, add/edit
#----------------------------------------------------------------------

// check if we have a template name
if(!(isset($params['template']) || isset($params['prefix'])))
{
	$params['errors'] = $this->Lang('error_params');
	$this->Redirect($id,'defaultadmin','',$params);
}

if(!isset($params['mode']) || !isset($params['title']))
{
	$params['errors'] = $this->Lang('error_params');
	$this->Redirect($id,'defaultadmin','',$params);
}

// handle errors
if(isset($params['errors']))
	echo $this->ShowErrors($params['errors']);

$params['origaction'] = $params['action'];
$contents = '';
if($params['mode'] == 'add')
{
	$smarty->assign('formstart',$this->CreateFormStart($id,'do_addtemplate',$returnid,'POST','','','',$params));
	$smarty->assign('templatename',$this->CreateInputText($id,'template','',40,200));
	$smarty->assign('hidden',
		$this->CreateInputHidden($id,'prefix',$params['prefix']).
		$this->CreateInputHidden($id,'activetab',$params['activetab']));
	if(!empty($params['defaulttemplatepref']))
	{
		if(endswith($params['defaulttemplatepref'],'.tpl'))
		{
			$fp = cms_join_path($this->GetModulePath(),'templates',$params['defaulttemplatepref']);
			$contents = @file_get_contents($fp);
		}
		else
		{
			$contents = $this->GetTemplate($params['defaulttemplatepref']);
			if(!$contents)
				 $contents = $this->GetPreference($params['defaulttemplatepref']);
		}
	}
}
else
{
	$smarty->assign('formstart',$this->CreateFormStart($id,'edittemplate',$returnid,'POST','','','',$params));
	$smarty->assign('templatename',$params['template']);
	$smarty->assign('hidden',
		$this->CreateInputHidden($id,'template',$params['template']).
		$this->CreateInputHidden($id,'activetab',$params['activetab']));
	$contents = $this->GetTemplate($params['prefix'].$params['template']);
	$smarty->assign('apply',$this->CreateInputSubmit($id,'applybutton',$this->Lang('apply')));
}

if(!empty($params['info']))
{
	$txt = trim($params['info']);
	for($i = 0; $i < 5; $i++)
	{
		$tmp = cms_html_entity_decode($txt);
		if($tmp == $txt)
			break;
		$txt = $tmp;
	}
	$smarty->assign('template_info',$txt);
}

if(isset($params['moddesc']))
	$smarty->assign('module_description',trim($params['moddesc']));

$title = trim($params['title']);
for($i = 0; $i < 5; $i++)
{
	$tmp = cms_html_entity_decode($title);
	if($tmp == $title)
		break;
	$title = $tmp;
}
$smarty->assign('title',cms_html_entity_decode($title));

$smarty->assign('prompt_templatename',$this->Lang('prompt_templatename'));
$smarty->assign('prompt_template',$this->Lang('prompt_template'));
$smarty->assign('template',$this->CreateSyntaxArea($id,$contents,'templatecontent'));
$smarty->assign('submit',$this->CreateInputSubmit($id,'submit',$this->Lang('submit')));
$smarty->assign('cancel',$this->CreateInputSubmit($id,'cancel',$this->Lang('cancel')));
$smarty->assign('formend',$this->CreateFormEnd());

echo $this->ProcessTemplate('edit_template.tpl');
?>
