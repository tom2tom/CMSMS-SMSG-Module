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

//
// initialize
//
$key = '';
$smstext = '';
$urlonly = 0;
$linktext = $this->Lang('send_to_mobile');
$inline = 0;

//
// Get Params
//
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
$key = md5($text);

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
#
# EOF
#
?>
