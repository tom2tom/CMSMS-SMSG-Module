<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
# action: entertext - create a link that when clicked will display a form
#  for the user to enter a message
#----------------------------------------------------------------------

if(isset($params['smsnum']))
{
	$smsnum = (int)$params['smsnum'];
	if($smsnum == '')
		return; //don't know who to send to
}
else
	return;

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
		$inline = 0;
		$returnid = $page;
	}
	unset($params['destpage']);
}

if(isset($params['linktext']))
{
	$linktext = trim($params['linktext']);
	unset($params['linktext']);
}
else
	$linktext = $this->Lang('send_me_message');

echo $this->CreateLink($id,'do_entertext',$returnid,$linktext,$params,'',
	$urlonly,$inline);

?>
