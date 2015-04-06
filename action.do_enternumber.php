<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

if( !isset($params['smskey']) ) return;  // no key

$inline = 0;
$thetemplate = $this->GetPreference(SMSG::PREF_DFLTENTERNUMBER_TPL);
$error = '';
$message = '';

// verify that the text is still in the database...
// and touch it just incase it would get expired.
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

	// handle form submission
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

?>
