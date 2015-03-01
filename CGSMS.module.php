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

///////////////////////////////////////////////////////////////////////////
// This module is derived from CGExtensions 
$cgextensions = cms_join_path($gCms->config['root_path'],'modules',
			      'CGExtensions','CGExtensions.module.php');
if( !is_readable( $cgextensions ) )
{
  echo '<h1><font color="red">ERROR: The CGExtensions module could not be found.</font></h1>';
  return;
}
require_once($cgextensions);
///////////////////////////////////////////////////////////////////////////

class CGSMS extends CGExtensions
{

  const TEST_MESSAGE = 'Test Message from CGSMS';
  const PREF_NEWENTERNUMBER_TPL = 'enternumber_newtpl';
  const PREF_DFLTENTERNUMBER_TPL = 'enternumber_dflttpl';
  const PREF_NEWENTERTEXT_TPL = 'entertext_newtpl';
  const PREF_DFLTENTERTEXT_TPL = 'entertext_dflttpl';
  

  /*---------------------------------------------------------
   Constructor()
   ---------------------------------------------------------*/
  public function __construct()
  {
    parent::__construct();
  }


  /*---------------------------------------------------------
   GetName()
   ---------------------------------------------------------*/
  public function GetName()
  {
    return 'CGSMS';
  }


  /*---------------------------------------------------------
   GetFriendlyName()
   ---------------------------------------------------------*/
  public function GetFriendlyName()
  {
    return $this->Lang('friendlyname');
  }


  /*---------------------------------------------------------
   GetVersion()
   ---------------------------------------------------------*/
  public function GetVersion()
  {
    return '1.0';
  }


  /*---------------------------------------------------------
   GetHelp()
   ---------------------------------------------------------*/
  public function GetHelp()
  {
    return $this->Lang('help');
  }


  /*---------------------------------------------------------
   GetAuthor()
   ---------------------------------------------------------*/
  public function GetAuthor()
  {
    return 'calguy1000';
  }


  /*---------------------------------------------------------
   GetAuthorEmail()
   ---------------------------------------------------------*/
  public function GetAuthorEmail()
  {
    return 'calguy1000@cmsmadesimple.org';
  }


  /*---------------------------------------------------------
   GetChangeLog()
   ---------------------------------------------------------*/
  public function GetChangeLog()
  {
    $txt = @file_get_contents(dirname(__FILE__).'/changelog.inc');
    return $txt;
  }
  
  /*---------------------------------------------------------
   IsPluginModule()
   ---------------------------------------------------------*/
  public function IsPluginModule()
  {
    return true;
  }


  /*---------------------------------------------------------
   HasAdmin()
   ---------------------------------------------------------*/
  public function HasAdmin()
  {
    return true;
  }


  /*---------------------------------------------------------
   GetAdminSection()
   ---------------------------------------------------------*/
  public function GetAdminSection()
  {
    return 'extensions';
  }


  /*---------------------------------------------------------
   GetAdminDescription()
   ---------------------------------------------------------*/
  public function GetAdminDescription()
  {
    return $this->Lang('module_description');
  }


  /*---------------------------------------------------------
   VisibleToAdminUser()
   ---------------------------------------------------------*/
  public function VisibleToAdminUser()
  {
    return $this->CheckPermission('Modify Site Preferences') ||
      $this->CheckPermission('Modify Templates');
  }


  /*---------------------------------------------------------
   GetDependencies()
   ---------------------------------------------------------*/
  public function GetDependencies()
  {
    return array('CGExtensions'=>'1.17.7');
  }


  /*---------------------------------------------------------
   InstallPostMessage()
   ---------------------------------------------------------*/
  public function InstallPostMessage()
  {
    return $this->Lang('postinstall');
  }


  /*---------------------------------------------------------
   MinimumCMSVersion()
   ---------------------------------------------------------*/
  public function MinimumCMSVersion()
  {
    return "1.6.5";
  }


  /*---------------------------------------------------------
   UninstallPostMessage()
   ---------------------------------------------------------*/
  public function UninstallPostMessage()
  {
    return $this->Lang('postuninstall');
  }


  /*---------------------------------------------------------
   AllowAutoInstall()
   ---------------------------------------------------------*/
  public function AllowAutoInstall() 
  {
    return FALSE;
  }


  /*---------------------------------------------------------
   AllowAutoUpgrade()
   ---------------------------------------------------------*/
  public function AllowAutoUpgrade() 
  {
    return FALSE;
  }


  /*---------------------------------------------------------
   SetParameters()
   ---------------------------------------------------------*/
  public function SetParameters()
  {
    $this->RegisterModulePlugin();
    $this->RestrictUnknownParams();

    $this->RegisterRoute('/CGSMS\/devreport$/',array('action'=>'devreport'));

    $this->CreateParameter('action','enternumber',$this->Lang('help_action'));

    $this->CreateParameter('smstext','',$this->Lang('help_smstext'));
    $this->SetParameterType('smstext',CLEAN_STRING);

    $this->CreateParameter('linktext',$this->Lang('send_to_mobile'),$this->Lang('help_linktext'));
    $this->SetParameterType('linktext',CLEAN_STRING);

    $this->CreateParameter('urlonly',0,$this->Lang('help_urlonly'));
    $this->SetParameterType('urlonly',CLEAN_INT);

    $this->CreateParameter('inline',0,$this->Lang('help_inline'));
    $this->SetParameterType('inline',CLEAN_INT);

    $this->CreateParameter('enternumbertemplate','',$this->Lang('help_enternumbertemplate'));
    $this->SetParameterType('enternumbertemplate',CLEAN_STRING);

    $this->CreateParameter('entertexttemplate','',$this->Lang('help_enternumbertemplate'));
    $this->SetParameterType('entertexttemplate',CLEAN_STRING);

    $this->CreateParameter('destpage',0,$this->Lang('help_destpage'));
    $this->SetParameterType('destpage',CLEAN_STRING);

    $this->SetParameterType('smskey',CLEAN_STRING);
    $this->SetParameterType(CLEAN_REGEXP.'/cgsms_.*/',CLEAN_NONE);

    $this->CreateParameter('smsnum','',$this->Lang('help_smsnum'));
    $this->SetParameterType('smsnum',CLEAN_INT);
  }


  /*---------------------------------------------------------
   GetHeaderHTML()
   ---------------------------------------------------------*/
  function GetHeaderHTML()
  {
    $obj =& $this->GetModuleInstance('JQueryTools');
    if( is_object($obj) )
      {
$tmpl = <<<EOT
{JQueryTools action='incjs' exclude='form'}
{JQueryTools action='ready'}
EOT;
        $txt = $this->ProcessTemplateFromData($tmpl);
	return $txt;
      }
  }	

} // end of class

#
# EOF
#
?>
