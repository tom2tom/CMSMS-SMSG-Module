<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

if( !isset($params['smskey']) )
	return;  // no key
// verify that the text is still cached
$key = trim($params['smskey']);
$datastore = new cge_datastore();
$smstext = $datastore->get($this->GetName(),$key);
if( !$smstext )
	return;  // nothing to do
// and touch the text to prevent expiry
$datastore->erase($this->GetName(),$key);
$datastore->store($smstext,$this->GetName(),$key);

$message = '';
$error = '';

if( isset($params['smsg_submit']) )
{
	// handle form submission
	if( isset($params['smsg_mobile']) )
	{
		$mobile = trim($params['smsg_mobile']);
		if( smsg_utils::is_valid_phone($mobile) )
		{
			// now we're ready to send
			$title = ( empty($params['gatename']) ) ? FALSE : $params['gatename'];
			$gateway = smsg_utils::get_gateway($title,$this);
			if( $gateway )
			{
				$gateway->set_msg($smstext);
				$gateway->set_num($mobile);
				$gateway->send();

				if( $gateway->get_status() == sms_gateway_base::STAT_OK )
					$message = $this->Lang('sms_message_sent');
				else
					$error = $gateway->get_statusmsg();
			}
			else
			{
				$error = $this->Lang('error_nogatewayfound');
				$this->Audit(SMSG::AUDIT_ERR,SMSG::MODNAME,'enternumber:'.$error);
			}
		}
		else
			$error = $this->Lang('error_invalid_number');
	}
}

// now display the form
$smarty->assign('message',$message);
$smarty->assign('error',$error);
if( !empty($params['gatename']) )
	$smarty->assign('gatename',$params['gatename']);
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_enternumber',
	$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());

if( empty($params['enternumbertemplate']) )
	$thetemplate = $this->GetPreference(SMSG::PREF_ENTERNUMBER_TPLDFLT);
else
	$thetemplate = trim($params['enternumbertemplate']);
echo $this->ProcessTemplateFromDatabase('enternumber_'.$thetemplate);

?>
