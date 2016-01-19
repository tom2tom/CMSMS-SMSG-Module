<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$gateway = smsg_utils::get_gateway(FALSE,$this); //only for the default gateway!?
// downstream must parse $_REQUEST directly
$msg = $gateway->process_delivery_report();
if($msg && $this->GetPreference('logdeliveries'))
	$this->Audit(SMSG::AUDIT_DELIV,SMSG::MODNAME,$msg);

$this->SendEvent('SMSDeliveryReported',array(
	'gateway'=>$gateway->get_name(),
	'status'=>$gateway->get_status(),
	'message'=>$msg,
	'timestamp'=>strftime('%X %Z')));

//clear all page content echoed before now
$handlers = ob_list_handlers();
if($handlers)
{
	$l = count($handlers);
	for ($c = 0; $c < $l; $c++)
		ob_end_clean();
}

exit;

?>
