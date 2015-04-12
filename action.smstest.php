<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$this->SetCurrentTab('test');

if( isset($params['submit']) )
{
	$number = '';
	if( isset($params['mobile']) )
		$number = trim($params['mobile']);

	if( smsg_utils::is_valid_phone($number) )
	{
		// ready to test (default gateway)
		$gateway = smsg_utils::get_gateway(FALSE,$this);
		if( $gateway )
		{
			$gateway->set_num($number);
			$gateway->set_msg($this->Lang('test_message',SMSG::MODNAME.' @ '.strftime('%X %Z'));
			$gateway->send();
			$status = $gateway->get_status();
			$msg = $gateway->get_statusmsg();
			if( $status == sms_gateway_base::STAT_OK )
				$this->SetMessage($msg);
			else
				$this->SetError($msg);
		}
		else
			$this->SetError($this->Lang('error_nogateway'));
	}
	else
		$this->SetError($this->Lang('error_invalidnumber'));
}

$this->RedirectToTab($id);

?>
