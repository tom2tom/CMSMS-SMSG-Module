<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
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
$this->SetPreference('sms_hourlylimit',(int)$params['hourlylimit']);
$this->SetPreference('sms_dailylimit',(int)$params['dailylimit']);
if( isset($params['masterpw']) )
  {
    $oldpw = $this->GetPreference('masterpass');
	$newpw = trim($params['masterpw']);
	if( $oldpw != $newpw )
      {
    //update current passwords
	$e = new Encryption(MCRYPT_BLOWFISH,MCRYPT_MODE_CBC,10000);
	$pref = cms_db_prefix();
    $sql = 'SELECT gate_id,title,value FROM '.$pref.'module_cgsms_props WHERE apiconvert>=80';
	$rows = $db->Execute($sql);
    if( $rows )
      {
    $sql = 'UPDATE '.$pref.'module_cgsms_props SET value=? WHERE gate_id=? AND title=?';
    foreach( $rows as &$onerow )
      {
        if( $oldpw )
          $raw = ($onerow['value']) ? $e->decrypt($onerow['value'],$oldpw) : '';
        else
          $raw = $onerow['value'];
        if( $raw )
          {
            if( $newpw )
              $revised = $e->encrypt($raw,$newpw);
            else
              $revised = $raw;
          }
        else
          $revised = NULL;
    	$db->Execute($sql,array($revised,$onerow['gate_id'],$onerow['title']))
      }
    unset( $onerow );
      }
	$this->SetPreference('masterpass',$newpw);
      }
  }

$this->RedirectToTab($id);
#
# EOF
#
?>
