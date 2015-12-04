<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$this->SetCurrentTab('security');
$this->SetPreference('hourlimit',(int)$params['hourlimit']);
$this->SetPreference('daylimit',(int)$params['daylimit']);
$this->SetPreference('logsends',!empty($params['logsends']));
$this->SetPreference('logdays',(int)$params['logdays']);
$this->SetPreference('logdeliveries',!empty($params['logdeliveries']));
if(isset($params['masterpass']))
{
	$oldpw = $this->GetPreference('masterpass');
	if($oldpw)
	{
		$s = base64_decode(substr($oldpw,5));
		$oldpw = substr($s,5);
	}
	$newpw = trim($params['masterpass']);
	if($oldpw != $newpw)
	{
		//update current passwords
		$pref = cms_db_prefix();
		$sql = 'SELECT gate_id,title,value,encvalue FROM '.$pref.'module_smsg_props WHERE encrypt>0';
		$rows = $db->GetAll($sql);
		if($rows)
		{
			$e = ($this->havemcrypt) ?
			 new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS) : FALSE;
			if($e && $newpw)
			{
				$tofield = 'encvalue';
				$notfield = 'value';
				$encval = 1;
			}
			else
			{
				$tofield = 'value';
				$notfield = 'encvalue';
				$encval = 0;
			}
			$sql = 'UPDATE '.$pref.'module_smsg_props SET '.$tofield.'=?,'.$notfield.'=NULL,encrypt=? WHERE gate_id=? AND title=?';
			foreach($rows as &$onerow)
			{
				if($oldpw)
					$raw = ($e && $onerow['encvalue']) ? $e->decrypt($onerow['encvalue'],$oldpw) : $onerow['encvalue'];
				else
					$raw = $onerow['value'];
				if($newpw)
					$revised = ($raw && $e) ? $e->encrypt($raw,$newpw) : $raw;
				else
					$revised = $raw;
				if(!$revised)
					$revised = NULL;
				$db->Execute($sql,array($revised,$encval,$onerow['gate_id'],$onerow['title']));
			}
			unset($onerow);
		}
		if($newpw)
		{
			$s = substr(base64_encode(md5(microtime())),0,5); //obfuscate
			$newpw= $s.base64_encode($s.$newpw);
		}
		$this->SetPreference('masterpass',$newpw);
	}
}

$this->RedirectToTab($id);

?>
