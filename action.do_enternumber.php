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
if( !isset($params['smskey']) ) return;  // no key.

//
// initialize
//
$inline = 0;
$thetemplate = $this->GetPreference(CGSMS::PREF_DFLTENTERNUMBER_TPL);
$error = '';
$message = '';

//
// verify that the text is still in the database...
// and touch it just incase it would get expired.
//
$key = trim($params['smskey']);
$datastore = new cge_datastore();
$smstext = $datastore->get($this->GetName(),$key);
if( !$smstext ) return;  // nothing to do.
$datastore->erase($this->GetName(),$key);
$datastore->store($smstext,$this->GetName(),$key);

if( isset($params['enternumbertemplate']) )
  {
    $thetemplate = trim($params['enternumbertemplate']);
  }

if( isset($params['cgsms_submit']) )
  {
    $mobile = '';

    //
    // handle form submission
    //
    if( isset($params['cgsms_mobile']) )
      {
	$mobile = trim($params['cgsms_mobile']);
      }

    // data validation
    if( !cgsms_utils::is_valid_phone($mobile) )
      {
	$error = $this->Lang('error_invalid_number');
      }

    if( !$error )
      {
	// now wer're ready to send.

	$gateway = cgsms_utils::get_gateway();
	if( !$gateway )
	  {
	    $this->Audit(0,$this->Lang('error_nogatewayfound'),'enternumber');
	    $error = $this->Lang('error_nogatewayfound');
	  }
	else
	  {
	    $gateway->set_msg($smstext);
	    $gateway->set_num($mobile);
	    $gateway->send();

	    $stat = $gateway->get_status();
	    $msg = $gateway->get_statusmsg();
	    if( $stat != cgsms_sender_base::STAT_OK )
	      {
		$error = $msg;
	      }
	    else
	      {
		$message = $this->Lang('sms_message_sent');
	      }
	  }
      }
  }

// now display the form.
if( $error != '' )
  {
    $smarty->assign('error',$error);
  }
if( $message != '' )
  {
    $smarty->assign('message',$message);
  }
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_enternumber',
						     $returnid,
						     $params));
$smarty->assign('formend',$this->CreateFormEnd());

echo $this->ProcessTemplateFromDatabase('enternumber_'.$thetemplate);

#
# EOF
#
?>