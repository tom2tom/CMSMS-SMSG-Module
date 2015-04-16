<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

if( !isset($params['smsnum']) ) return;  // no number id

$smstext = '';
$message = '';

// check that the supplied SMS ID is valid
$smsnum = (int)$params['smsnum'];
$query = 'SELECT mobile FROM '.cms_db_prefix().'module_smsg_nums WHERE id=?';
$mobile = $db->GetOne($query,array($smsnum));
$error = ( $mobile ) ? '' : $this->Lang('error_notfound');

if( !$error && isset($params['smsg_submit']) )
{
	if( isset($params['smsg_smstext']) )
		$smstext = trim($params['smsg_smstext']);

	if( smsg_utils::text_is_valid($smstext) )
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
				$message = $this->Lang('sms_message_sent',$mobile);
			else
				$error = $gateway->get_statusmsg();
		}
		else
		{
			$error = $this->Lang('error_nogatewayfound');
			$this->Audit(SMSG::AUDIT_ERR,SMSG::MODNAME,'entertext:'.$error);
		}
	}
	else
		$error = $this->Lang('error_invalid_text');
}

// now display the form
$smarty->assign('message',$message);
$smarty->assign('error',$error);
if( !empty($params['gatename']) )
	$smarty->assign('gatename',$params['gatename']);
$smarty->assign('maxsmschars',160);
$smarty->assign('smstext',$smstext);
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_entertext',
	$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());
if( !isset($params['enternumbertemplate']) )
	$thetemplate = $this->GetPreference(SMSG::PREF_ENTERTEXT_TPLDFLT);
else
	$thetemplate = trim($params['enternumbertemplate']);
echo $this->ProcessTemplateFromDatabase('entertext_'.$thetemplate);

?>
