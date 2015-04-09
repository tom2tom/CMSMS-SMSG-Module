<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

if( !isset($params['smsnum']) ) return;  // no number id

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
$smstext = '';

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
		$gateway = smsg_utils::get_gateway($this);
		if( $gateway )
		  {
			$gateway->set_msg($smstext);
			$gateway->set_num($mobile);
			$gateway->send();

			$stat = $gateway->get_status();
			$msg = $gateway->get_statusmsg();
			if( $stat == sms_gateway_base::STAT_OK )
				$message = $this->Lang('sms_message_sent',$mobile);
			else
				$error = $msg;
		  }
		else
		  {
			$error = $this->Lang('error_nogatewayfound');
			$this->Audit(0,$this->Lang('error_nogatewayfound'),'entertext');
		  }
	  }
  }

// now display the form
$smarty->assign('message',$message);
$smarty->assign('error',$error);
$smarty->assign('maxsmschars',160);
$smarty->assign('smstext',$smstext);
$smarty->assign('formstart',$this->CGCreateFormStart($id,'do_entertext',
	$returnid,$params));
$smarty->assign('formend',$this->CreateFormEnd());
$js = <<<EOS
<script type="text/javascript">
//<![CDATA[{literal}
var st = null, cl = null;
function smsg_entertext_onchange() {
 if(st == null) st = document.getElementById('smsg_entertext_smstext');
 var charsleft = 160 - st.value.length - 1;
 if(charsleft < 0) return false;
 if(cl == null) cl = document.getElementById('smsg_entertext_charsleft');
 cl.innerHTML = charsleft;
 return true;
}
//]]>{/literal}
</script>

EOS;
$smarty->assign('js',$js);
echo $this->ProcessTemplateFromDatabase('entertext_'.$thetemplate);

?>
