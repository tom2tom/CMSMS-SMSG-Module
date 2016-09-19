<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
# action: do_entertext - display a form for the user to enter a message
#----------------------------------------------------------------------

if(!isset($params['smsnum']))
	return;  // no number id

$smstext = '';
$message = '';

// check that the supplied SMS ID is valid
$smsnum = (int)$params['smsnum'];
$query = 'SELECT mobile FROM '.cms_db_prefix().'module_smsg_nums WHERE id=?';
$mobile = $db->GetOne($query,array($smsnum));
$error = ($mobile) ? '' : $this->Lang('error_notfound');

if(!$error && isset($params['smsg_submit']))
{
	if(isset($params['smsg_smstext']))
		$smstext = trim($params['smsg_smstext']);

	if(smsg_utils::text_is_valid($smstext))
	{
		// now we're ready to send
		$title = (empty($params['gatename'])) ? FALSE : $params['gatename'];
		$gateway = smsg_utils::get_gateway($title,$this);
		if($gateway)
		{
			$gateway->set_msg($smstext);
			$gateway->set_num($mobile);
			$gateway->send();

			if($gateway->get_status() == base_sms_gateway::STAT_OK)
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

// display the form
$tplvars = array();
$tplvars['message'] = $message;
$tplvars['error'] = $error;
if(!empty($params['gatename']))
	$tplvars['gatename'] = $params['gatename'];
$tplvars['maxsmschars'] = 160;
$tplvars['smstext'] = $smstext;
$tplvars['formstart'] = $this->CreateFormStart($id,'do_entertext',$returnid,'POST','','','',$params);
$tplvars['formend'] = $this->CreateFormEnd();
if(!isset($params['enternumbertemplate']))
	$thetemplate = $this->GetPreference(SMSG::PREF_ENTERTEXT_TPLDFLT);
else
	$thetemplate = trim($params['enternumbertemplate']);
echo smsg_utils::ProcessTemplateFromDatabase($this,'entertext_'.$thetemplate,$tplvars);
?>
