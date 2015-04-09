<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

$key = '';
$smstext = '';
$urlonly = 0;
$linktext = $this->Lang('send_to_mobile');
$inline = 0;

if( isset($params['smstext']) )
  {
	$smstext = trim($params['smstext']);
	unset($params['smstext']);
  }
if( isset($params['linktext']) )
  {
	$linktext = trim($params['linktext']);
	unset($params['linktext']);
  }
if( isset($params['urlonly']) )
  {
	$urlonly = (int)$params['urlonly'];
	unset($params['urlonly']);
  }
if( isset($params['inline']) )
  {
	$inline = (int)$params['inline'];
  }
if( isset($params['destpage']) )
  {
	$page = $this->resolve_alias_or_id($params['destpage']);
	if( $page )
	  {
		$returnid = $page;
		$inline = 0;
	  }
	unset($params['destpage']);
  }
if( $smstext == '' )
  {
	// could not find text
	return;
  }

// given the text... get a key
$key = md5($smstext);

// store the data in the temporary data store.
// in case this is a realoaded page, make sure we erase the data first
$datastore = new cge_datastore();
$datastore->erase($this->GetName(),$key);
$datastore->store($smstext,$this->GetName(),$key);

// Now create a link.
$params['smskey'] = $key;
$txt = $this->CreateLink($id,'do_enternumber',$returnid,$linktext,$params,
	'',$urlonly,$inline);
echo $txt;

?>
