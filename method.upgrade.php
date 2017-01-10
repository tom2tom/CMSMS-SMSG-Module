<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

function rmdir_recursive($dir)
{
	foreach(scandir($dir) as $file) {
		if (!($file === '.' || $file === '..')) {
			$fp = $dir.DIRECTORY_SEPARATOR.$file;
			if (is_dir($fp))
				rmdir_recursive($fp);
			else
				@unlink($fp);
		}
	}
	rmdir($dir);
}

$db = cmsms()->GetDb();
$dict = NewDataDictionary($db);
$pref = cms_db_prefix();
$taboptarray = ['mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
 'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci'];

switch($oldversion)
{
case '0.9':
case '1.0':
case '1.0.1':
	//remove files now renamed
	$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
	$bases = ['Encryption','sms_gateway_base'];
	foreach ($bases as $base)
	{
		$fp = $path.'class.'.$base.'.php';
		if(@is_file($fp)) unlink($fp);
	}
	//remove redundant gateway lib
	$fp = $path.'twilio';
	if(@is_dir($fp)) {
		rmdir_recursive($fp);
	}
}
?>
