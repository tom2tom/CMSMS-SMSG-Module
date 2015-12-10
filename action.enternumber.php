<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
# action: enternumber - create a link that when clicked will display a form
#  for the user to enter a phone number
#----------------------------------------------------------------------

if(isset($params['smstext']))
{
	$smstext = trim($params['smstext']);
	if($smstext == '')
		return;
	unset($params['smstext']);
}
else
	return;

if(isset($params['linktext']))
{
	$linktext = trim($params['linktext']);
	unset($params['linktext']);
}
else
	$linktext = $this->Lang('send_to_mobile');

if(isset($params['urlonly']))
{
	$urlonly = (int)$params['urlonly'];
	unset($params['urlonly']);
}
else
	$urlonly = 0;

if(isset($params['inline']))
	$inline = (int)$params['inline'];
else
	$inline = 0;

if(isset($params['destpage']))
{
	$page = $this->resolve_alias_or_id($params['destpage']);
	if($page)
	{
		$returnid = $page;
		$inline = 0;
	}
	unset($params['destpage']);
}

// cache the message
$key = uniqid($smstext);
$this->SetPreference($key,$smstext);

$params['smskey'] = $key;
echo $this->CreateLink($id,'do_enternumber',$returnid,$linktext,$params,'',
	$urlonly,$inline);

?>
