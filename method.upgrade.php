<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

function rmdir_recursive($dir)
{
	foreach(scandir($dir) as $file) {
		if (!($file === '.' || $file === '..')) {
			$fp = $dir.DIRECTORY_SEPARATOR.$file;
			if (is_dir($fp)) {
				rmdir_recursive($fp);
			} else {
 				@unlink($fp);
			}
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
 case '1.1':
	//renamed files
	$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
	$bases = ['Encryption','sms_gateway_base'];
	foreach ($bases as $base) {
		$fp = $path.'class.'.$base.'.php';
		if (@is_file($fp)) unlink($fp);
	}
	//redundant gateway lib
	$fp = $path.'twilio';
	if (@is_dir($fp)) {
		rmdir_recursive($fp);
	}
	//redundant directory
	$file = cms_join_path(dirname(__FILE__), 'include');
	if (is_dir($file)) {
		rmdir_recursive($file);
	}
	$t = 'nQCeESKBr99A';
	$this->SetPreference($t, hash('sha256', $t.microtime()));
	$cfuncs = new SMSG\Crypter($this);
	$key = 'masterpass';
	$pw = $this->GetPreference($key);
	if ($pw) {
		$s = base64_decode(substr($pw,5));
		$pw = substr($s,5);
	}
	if (!$pw) {
		$pw = base64_decode('RW50ZXIgYXQgeW91ciBvd24gcmlzayEgRGFuZ2Vyb3VzIGRhdGEh');
	}
	$this->RemovePreference($key);
	$cfuncs->init_crypt();
	$cfuncs->encrypt_preference(SMSG\Crypter::MKEY,$pw);
 case '1.2':
	if (!isset($cfuncs)) {
		$cfuncs = new SMSG\Crypter($this);
		$key = 'masterpass';
		$s = base64_decode($this->GetPreference($key));
		$t = $config['ssl_url'].$this->GetModulePath();
		$val = hash('crc32b',$this->GetPreference('nQCeESKBr99A').$t);
		$pw = $cfuncs->decrypt($s,$val);
		if (!$pw) {
			$pw = base64_decode('RW50ZXIgYXQgeW91ciBvd24gcmlzayEgRGFuZ2Vyb3VzIGRhdGEh');
		}
		$this->RemovePreference($key);
		$cfuncs->init_crypt();
		$cfuncs->encrypt_preference(SMSG\Crypter::MKEY,$pw);
	}
	$this->RemovePreference('nQCeESKBr99A');
}
