<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

// receive delivery reports
// must parse $_REQUEST directly

$gateway = smsg_utils::get_gateway($this);
$msg = $gateway->process_delivery_report();
//TODO make this prerential
if( $msg )
  {
	$this->Audit('',$this->GetName(),$msg);
  }

?>
