<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

echo $this->ShowTemplateList($id,$returnid,'entertext_',
	SMSG::PREF_NEWENTERTEXT_TPL,'entertext',
	SMSG::PREF_DFLTENTERTEXT_TPL,
	$this->Lang('title_entertext_templates'),
	$this->Lang('info_entertext_templates'));

?>
