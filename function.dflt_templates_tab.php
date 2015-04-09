<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$smarty->assign('defaultnumber',
	$this->GetDefaultTemplateForm($this,$id,$returnid,
	SMSG::PREF_NEWENTERNUMBER_TPL,'defaultadmin','dflt_templates',
	$this->Lang('dflt_enternumber_template'),
	'orig_enternumber_template.tpl',
	$this->Lang('info_sysdflt_enternumber_template')));
$smarty->assign('defaulttext',
	$this->GetDefaultTemplateForm($this,$id,$returnid,
	SMSG::PREF_NEWENTERTEXT_TPL,'defaultadmin','dflt_templates',
	$this->Lang('dflt_entertext_template'),
	'orig_entertext_template.tpl',
	$this->Lang('info_sysdflt_entertext_template')));

?>
