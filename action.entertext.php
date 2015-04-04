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
$smsnum = '';
$smstext = '';
$urlonly = 0;
$linktext = $this->Lang('send_me_message');
$inline = 0;

//
// Get Params
//
if( isset($params['smsnum']) )
  {
	$smsnum = (int)$params['smsnum'];
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
		$inline = 0;
		$returnid = $page;
	  }
	unset($params['destpage']);
  }
if( isset($params['linktext']) )
  {
	$linktext = trim($params['linktext']);
	unset($params['linktext']);
  }
if( $smsnum == '' )
  {
	// don't know who to send to
	return;
  }

// create a Link
$txt = $this->CreateLink($id,'do_entertext',$returnid,$linktext,
	$params,'',$urlonly,$inline);
echo $txt;
#
# EOF
#
?>
