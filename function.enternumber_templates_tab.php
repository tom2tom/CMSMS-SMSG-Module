<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

echo $this->ShowTemplateList($id,$returnid,'enternumber_',
	SMSG::PREF_NEWENTERNUMBER_TPL,'enternumber',
	SMSG::PREF_DFLTENTERNUMBER_TPL,
	$this->Lang('title_enternumber_templates'),
	$this->Lang('info_enternumber_templates'));

?>
