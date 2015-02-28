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

$db =& $this->GetDb();
$taboptarray = array('mysql' => 'TYPE=MyISAM');

$dict = NewDataDictionary($db);
$flds = "
         id I KEY AUTO,
         mobile C(25),
         name   C(25) KEY
        ";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix()."module_cgsms", 
		$flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "mobile C(25),
         ip     C(25),
         msg    C(160),
         sdate  ".CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL(cms_db_prefix().'module_cgsms_sent',$flds,$taboptarray);
$dict->ExecuteSQLArray($sqlarray);

# enter number templates
$fn = dirname(__FILE__).'/templates/orig_enternumber_template.tpl';
if( file_exists($fn) )
  {
    $template = file_get_contents($fn);
    $this->SetPreference(CGSMS::PREF_NEWENTERNUMBER_TPL,$template);
    $this->SetTemplate('enternumber_Sample',$template);
    $this->SetPreference(CGSMS::PREF_DFLTENTERNUMBER_TPL,'Sample');
  }

# enter text templates
$fn = dirname(__FILE__).'/templates/orig_entertext_template.tpl';
if( file_exists($fn) )
  {
    $template = file_get_contents($fn);
    $this->SetPreference(CGSMS::PREF_NEWENTERTEXT_TPL,$template);
    $this->SetTemplate('entertext_Sample',$template);
    $this->SetPreference(CGSMS::PREF_DFLTENTERTEXT_TPL,'Sample');
  }
#
# EOF
#
?>