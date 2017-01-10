<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if(!($this->CheckPermission('AdministerSMSGateways')
  || $this->CheckPermission('ModifySMSGateTemplates'))) exit;

if(empty($params['mode'])) //we're back from edittemplate action
	$this->Redirect($id,'defaultadmin','',['activetab'=>$params['activetab']]);

$name = $params['template'];
$pref = $params['prefix'];
switch($params['mode'])
{
 case 'add':
	if(isset($params['cancel']) || isset($params['submit'])) //2nd-pass, after addition
	{
		if(isset($params['submit']))
		{
			if($this->before20)
			{
				$this->SetTemplate($pref.$name,$params['templatecontent']);
			}
			else
			{
				$uid = get_userid(FALSE);
				$type = rtrim($pref,'_');
				try {
					$tpl = new CmsLayoutTemplate();
					$tpl->set_type($type);
					$tpl->set_name($pref.$name);
					$tpl->set_owner($uid);
					$tpl->set_content($params['templatecontent']);
					$tpl->save();
				} catch (Exception $e) {
					$this->SetError($e->getMessage());
				}
			}
		}
		$this->Redirect($id,'defaultadmin','',['activetab'=>$params['activetab']]);
	}
	//1st-pass: setup for handover to edittemplate action
	$params['defaulttemplatepref'] = $pref.'defaultcontent';
 case 'edit':
	$params['moddesc'] = $this->GetFriendlyName();
	$params['modname'] = $this->GetName();
	$params['destaction'] = 'settemplate'; //come back here when done
	switch($pref)
	{
	 case 'entertext_':
		//title displayed in add/edit template form
		$params['title'] = $this->Lang('title_entertext_templates');
		//information text displayed in add/edit template form
		$params['info'] = $this->Lang('info_entertext_templates');
		break;
	 case 'enternumber_':
		$params['title'] = $this->Lang('title_enternumber_templates');
		$params['info'] = $this->Lang('info_enternumber_templates');
	 	break;
	 default:
		$params['title'] = '';
		$params['info'] = '';
	 	break;
	}
	$this->Redirect($id,'edittemplate','',$params);
 case 'delete':
	if($this->before20)
		$this->DeleteTemplate($pref.$name,SMSG::MODNAME);
	else
	{
		try {
			$tpl = CmsLayoutTemplate::load($pref.$name);
			$tpl->delete();
		} catch (Exception $e) {
			$this->SetError($e->getMessage());
		}
	}
	break;
 case 'default':
	if($this->before20)
		$this->SetTemplate($pref.'defaultcontent',$this->GetTemplate($pref.$name),SMSG::MODNAME);
	else
	{
		try {
			$tpl = CmsLayoutTemplate::load($pref.$name);
			$text = $tpl->get_content();
			if($text)
			{
				$tpl = CmsLayoutTemplate::load($pref.'defaultcontent');
				$tpl->set_content($text);
				$tpl->save();
			}
			else
			{
				$this->SetError($this->Lang('error_notfound'));
				break;
			}
		} catch (Exception $e) {
			$this->SetError($e->getMessage());
			break;
		}
	}
	$this->SetPreference($pref.'dflttpl',$name);
	break;
 case 'revert':
	$fn = cms_join_path(dirname(__FILE__),'templates',$pref.'template.tpl');
	if(is_file($fn))
		$text = ''.@file_get_contents($fn);
	if($text)
	{
		if($this->before20)
		{
			$this->SetTemplate($pref.'defaultcontent',$text,SMSG::MODNAME);
		}
		else
		{
			try {
				$tpl = CmsLayoutTemplate::load($pref.'defaultcontent');
				$tpl->set_content($text);
				$tpl->save();
			} catch (Exception $e) {
				$this->SetError($e->getMessage());
				break;
			}
		}
		$this->SetMessage($this->Lang('template_saved'));
	}
	else
		$this->SetError($this->Lang('error_notfound'));
	break;
}

$this->Redirect($id,'defaultadmin','',['activetab'=>$params['activetab']]);

?>
