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
	  {
		$number = trim($params['mobile']);
	  }

	if( $number == '' || !smsg_utils::is_valid_phone($number) )
	  {
		$this->SetError($this->Lang('error_invalidnumber'));
	  }
	else
	  {
		// ready to test
		$sender = smsg_utils::get_gateway();
		if( !$sender )
		  {
			$this->SetError($this->Lang('error_nogateway'));
		  }
		else
		  {
			$sender->set_num($number);
			$sender->set_msg(SMSG::TEST_MESSAGE.' @'.strftime('%X %Z'));
			$sender->send();
			$status = $sender->get_status();
			$msg = $sender->get_statusmsg();
			if( $status != sms_gateway_base::STAT_OK )
			  {
				$this->SetError($msg);
			  }
			else
			  {
				$this->SetMessage($msg);
			  }
		  }
	  }
  }

$this->RedirectToTab($id);

?>
