<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: SMSG (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
# An addon module for CMS Made Simple to provide the ability for other
# modules to send SMS messages
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2015 Ted Kulp (wishy@cmsmadesimple.org)
# Its homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This file is free software; you can redistribute it and/or modify it
# under the terms of the GNU Affero General Public License as published
# by the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This file is distributed as part of an addon module for CMS Made Simple.
# As a special extension to the AGPL, you may not use this file in any
# non-GPL version of CMS Made Simple, or in any version of CMS Made Simple
# that does not indicate clearly and obviously in its admin section that
# the site was built with CMS Made Simple.
#
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
# Read the Licence online: http://www.gnu.org/licenses/licenses.html#AGPL
#-------------------------------------------------------------------------
#END_LICENSE

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
#
# EOF
#
?>
