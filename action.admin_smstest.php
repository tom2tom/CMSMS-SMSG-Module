<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (c) 2010 by Robert Campbell 
#         (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to provide the ability for other
#  modules to send SMS messages
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE
if( !isset($gCms) ) exit;

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