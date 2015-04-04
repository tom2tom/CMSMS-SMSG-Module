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

if( !isset($params['smsnum']) ) return;  // no number id

//
// initialize
//
$smsnum = (int)$params['smsnum'];
$inline = 0;
$thetemplate = $this->GetPreference(SMSG::PREF_DFLTENTERTEXT_TPL);
$error = '';
$message = '';

if( isset($params['enternumbertemplate']) )
  {
	$thetemplate = trim($params['enternumbertemplate']);
  }

//
// verify that the SMS ID entered is valid
//
$query = 'SELECT mobile FROM '.cms_db_prefix().'module_smsg WHERE id=?';
$mobile = $db->GetOne($query,array($smsnum));

if( isset($params['smsg_submit']) )
  {
	$smstext = '';
	if( isset($params['smsg_smstext']) )
	  {
		$smstext = trim($params['smsg_smstext']);
	  }

	if( !smsg_utils::text_is_valid($smstext))
	  {
		$error = $this->Lang('error_invalid_text');
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
			if( $stat == smsg_sender_base::STAT_OK )
			  {
				$message = $this->Lang('sms_message_sent',$mobile);
			  }
			else
			  {
				$error = $msg;
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
$smarty->assign('maxsmschars',160);
$smarty->assign('smstext',$smstext);
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_entertext',
	$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());

echo $this->ProcessTemplateFromDatabase('entertext_'.$thetemplate);
#
# EOF
#
?>
