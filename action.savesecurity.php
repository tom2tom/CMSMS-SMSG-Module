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
if( isset($params['masterpw']) )
  {
	$oldpw = $this->GetPreference('masterpass');
	if( $oldpw )
	  {
		$s = base64_decode(substr($oldpw,5));
		$oldpw = substr($s,5);
	  }
	$newpw = trim($params['masterpass']);
	if( $oldpw != $newpw )
	  {
		//update current passwords
		$e = ( $this->havemcrypt ) ?
		 new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,SMSG::ENC_ROUNDS) : FALSE;
		$pref = cms_db_prefix();
		$sql = 'SELECT gate_id,title,value FROM '.$pref.'module_smsg_props WHERE apiconvert>='.SMSG::DATA_PW;
		$rows = $db->GetAll($sql);
		if( $rows )
		  {
			$sql = 'UPDATE '.$pref.'module_smsg_props SET value=? WHERE gate_id=? AND title=?';
			foreach( $rows as &$onerow )
			  {
				if( $oldpw )
					$raw = ($e && $onerow['value']) ? $e->decrypt($onerow['value'],$oldpw) : $onerow['value'];
				else
					$raw = $onerow['value'];
				if( $raw )
				  {
					if( $newpw )
						$revised = ($e) ? $e->encrypt($raw,$newpw) : $raw;
					else
						$revised = $raw;
				  }
				else
					$revised = NULL;
				$db->Execute($sql,array($revised,$onerow['gate_id'],$onerow['title']))
			  }
			unset( $onerow );
		  }
		if( $newpw )
		  {
			$s = substr(base64_encode(md5(microtime())),0,5); //obfuscate
			$newpw= $s.base64_encode($s.$newpw);
		  }
		$this->SetPreference('masterpass',$newpw);
	  }
  }

$this->RedirectToTab($id);

?>
