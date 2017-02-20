<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
if(!$this->CheckPermission('AdministerSMSGateways')) exit;

if(isset($params['cancel']))
	$this->Redirect($id,'defaultadmin','',['activetab'=>'security']);

$this->SetPreference('hourlimit',(int)$params['hourlimit']);
$this->SetPreference('daylimit',(int)$params['daylimit']);
$this->SetPreference('logsends',!empty($params['logsends']));
$this->SetPreference('logdays',(int)$params['logdays']);
$this->SetPreference('logdeliveries',!empty($params['logdeliveries']));
if(isset($params['masterpass']))
{
	$oldpw = $this->GetPreference('masterpass');
	if($oldpw)
		$oldpw = smsg_utils::unfusc($oldpw);
	$newpw = trim($params['masterpass']);
	if($oldpw != $newpw)
	{
		//update current passwords
		$pref = cms_db_prefix();
		$sql = 'SELECT gate_id,title,value,encvalue FROM '.$pref.'module_smsg_props WHERE encrypt>0';
		$rows = $db->GetArray($sql);
		if($rows)
		{
			$e = ($this->havemcrypt) ?
				new Encryption('BF-CBC','default',SMSG::STRETCHES) : FALSE;
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
				$db->Execute($sql,[$revised,$encval,$onerow['gate_id'],$onerow['title']]);
			}
			unset($onerow);
		}
		if($newpw)
			$newpw = smsg_utils::fusc($newpw);

		$this->SetPreference('masterpass',$newpw);
	}
}

$this->Redirect($id,'defaultadmin','',['activetab'=>'security']);

?>
