<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (c) 2010 by Robert Campbell 
#         (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to provide the ability for other
#  modules to send SMS messages
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE
if( !isset($gCms) ) exit;

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
    // could not find text.
    return;
  }

// given the text... get a key
$key = md5($text);

// store the data in the temporary data store.
// just incase this is a realoaded page, make sure we erase
// the data first.
$datastore = new cge_datastore();
$datastore->erase($this->GetName(),$key);
$datastore->store($smstext,$this->GetName(),$key);

// Now create a link.
$params['smskey'] = $key;
$txt = $this->CreateLink($id,'do_enternumber',$returnid,$linktext,
			 $params,'',$urlonly,$inline);
echo $txt;
#
# EOF
#
?>