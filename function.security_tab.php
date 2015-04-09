<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$smarty->assign('hourlimit',$this->GetPreference('hourlimit'));
$smarty->assign('daylimit',$this->GetPreference('daylimit'));
$smarty->assign('logsends',$this->GetPreference('logsends'));
$smarty->assign('logdays',$this->GetPreference('logdays'));
$pw = $this->GetPreference('masterpass');
if( $pw )
  {
	$s = base64_decode(substr($pw,5));
	$pw = substr($s,5);
  }
$smarty->assign('masterpass',$pw);

?>
