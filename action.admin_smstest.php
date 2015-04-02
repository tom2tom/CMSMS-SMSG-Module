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

$this->SetCurrentTab('test');

if( isset($params['submit']) )
  {
    $number = '';
    if( isset($params['mobile']) )
      {
	$number = trim($params['mobile']);
      }

    if( $number == '' || !cgsms_utils::is_valid_phone($number) )
      {
	$this->SetError($this->Lang('error_invalidnumber'));
      }
    else
      {
	// ready to test
	$sender = cgsms_utils::get_gateway();
	if( !$sender )
	  {
	    $this->SetError($this->Lang('error_nogateway'));
	  }
	else
	  {
	    $sender->set_num($number);
	    $sender->set_msg(CGSMS::TEST_MESSAGE.' @'.strftime('%X %Z'));
	    $sender->send();
	    $status = $sender->get_status();
	    $msg = $sender->get_statusmsg();
	    if( $status != cgsms_sender_base::STAT_OK )
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
#
# EOF
#
?>
