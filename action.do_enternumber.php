<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: SMSG (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
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

if( !isset($params['smskey']) ) return;  // no key

//
// initialize
//
$inline = 0;
$thetemplate = $this->GetPreference(SMSG::PREF_DFLTENTERNUMBER_TPL);
$error = '';
$message = '';

//
// verify that the text is still in the database...
// and touch it just incase it would get expired.
//
$key = trim($params['smskey']);
$datastore = new cge_datastore();
$smstext = $datastore->get($this->GetName(),$key);
if( !$smstext ) return;  // nothing to do
$datastore->erase($this->GetName(),$key);
$datastore->store($smstext,$this->GetName(),$key);

if( isset($params['enternumbertemplate']) )
  {
	$thetemplate = trim($params['enternumbertemplate']);
  }

if( isset($params['smsg_submit']) )
  {
	$mobile = '';

	//
	// handle form submission
	//
	if( isset($params['smsg_mobile']) )
	  {
		$mobile = trim($params['smsg_mobile']);
	  }

	// data validation
	if( !smsg_utils::is_valid_phone($mobile) )
	  {
		$error = $this->Lang('error_invalid_number');
	  }

	if( !$error )
	  {
		// now we're ready to send

		$gateway = smsg_utils::get_gateway();
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
			if( $stat != smsg_sender_base::STAT_OK )
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

// now display the form
if( $error != '' )
  {
	$smarty->assign('error',$error);
  }
if( $message != '' )
  {
	$smarty->assign('message',$message);
  }
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_enternumber',
	$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());

echo $this->ProcessTemplateFromDatabase('enternumber_'.$thetemplate);

#
# EOF
#
?>
