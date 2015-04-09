<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$this->SetCurrentTab('settings');

$gateway = $params['sms_gateway']; //e.g. 'smsbroadcast' or -1
if( !(isset($params['submit']) || isset($params[$gateway.'~delete'])) )
	$this->RedirectToTab($id);

$objs = smsg_utils::get_gateways_full($this);
if( !$objs )
  {
	$this->RedirectToTab($id);
  }

if(isset($params['submit']))
  {
	$pref = cms_db_prefix();
	$sql = 'UPDATE '.$pref.'module_smsg_gates SET active=0 WHERE active=1';
	$db->Execute($sql);
	if( $gateway != '-1' )
	  {
		$sql = 'UPDATE '.$pref.'module_smsg_gates SET enabled=1,active=1 WHERE alias=?';
		$db->Execute($sql,array($gateway));
	  }
  }

foreach( $objs as $classname => $rec )
  {
	$rec['obj']->handle_setup_form($params);
  }

$this->RedirectToTab($id);

?>
