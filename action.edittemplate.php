<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright(C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
# action - edittemplate redirected here from action settemplate, add/edit
#----------------------------------------------------------------------
if(!($this->CheckPermission('AdministerSMSGateways')
  || $this->CheckPermission('ModifySMSGateTemplates'))) exit;

if(isset($params['cancel']))
	$this->Redirect($id,'defaultadmin','',array('activetab'=>$params['activetab']));

// check if we have a template name
if(!(isset($params['template']) || isset($params['prefix'])))
{
	$this->SetError($this->Lang('error_params'));
	$this->Redirect($id,'defaultadmin','',array('activetab'=>$params['activetab']));
}

if(!isset($params['mode']) || !isset($params['title']))
{
	$this->SetError($this->Lang('error_params'));
	$this->Redirect($id,'defaultadmin','',array('activetab'=>$params['activetab']));
}

// handle errors
if(isset($params['errors']))
	echo $this->ShowErrors($params['errors']);

$params['origaction'] = $params['action'];
$contents = '';
$tplvars = array();
if($params['mode'] == 'add')
{
	$tplvars['formstart'] = $this->CreateFormStart($id,'do_addtemplate',$returnid,'POST','','','',$params);
	$tplvars['templatename'] = $this->CreateInputText($id,'template','',40,200);
	$tplvars['hidden'] =
		$this->CreateInputHidden($id,'prefix',$params['prefix']).
		$this->CreateInputHidden($id,'activetab',$params['activetab']);
	if(!empty($params['defaulttemplatepref']))
	{
		if(endswith($params['defaulttemplatepref'],'.tpl'))
		{
			$fp = cms_join_path($this->GetModulePath(),'templates',$params['defaulttemplatepref']);
			$contents = @file_get_contents($fp);
		}
		else
		{
			if($this->before20)
				$contents = $this->GetTemplate($params['defaulttemplatepref']);
			else
				$contents = $TemplateTODO;
			if(!$contents)
				 $contents = $this->GetPreference($params['defaulttemplatepref']);
		}
	}
}
else
{
	$tplvars['formstart'] = $this->CreateFormStart($id,'edittemplate',$returnid,'POST','','','',$params);
	$tplvars['templatename'] = $params['template'];
	$tplvars['hidden'] =
		$this->CreateInputHidden($id,'template',$params['template']).
		$this->CreateInputHidden($id,'activetab',$params['activetab']);
	if($this->before20)
		$contents = $this->GetTemplate($params['prefix'].$params['template']);
	else
		$contents = $TemplateTODO;
	$tplvars['apply'] = $this->CreateInputSubmit($id,'applybutton',$this->Lang('apply'));
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
	$tplvars['template_info'] = $txt;
}

if(isset($params['moddesc']))
	$tplvars['module_description'] = trim($params['moddesc']);

$title = trim($params['title']);
for($i = 0; $i < 5; $i++)
{
	$tmp = cms_html_entity_decode($title);
	if($tmp == $title)
		break;
	$title = $tmp;
}

$tplvars += array(
	'title' => cms_html_entity_decode($title),

	'prompt_templatename' => $this->Lang('prompt_templatename'),
	'prompt_template' => $this->Lang('prompt_template'),
	'template' => $this->CreateSyntaxArea($id,$contents,'templatecontent'),
	'submit' => $this->CreateInputSubmit($id,'submit',$this->Lang('submit')),
	'cancel' => $this->CreateInputSubmit($id,'cancel',$this->Lang('cancel')),
	'formend' => $this->CreateFormEnd()
);

echo smsg_utils::ProcessTemplate($this,'edit_template.tpl',$tplvars);
?>
