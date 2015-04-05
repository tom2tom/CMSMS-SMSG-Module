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

///////////////////////////////////////////////////////////////////////////
// This module is derived from CGExtensions
$config = cmsms()->GetConfig();
$cgextensions = cms_join_path($config['root_path'],'modules','CGExtensions',
	'CGExtensions.module.php');
if( !is_readable($cgextensions) )
  {
	echo '<h1 style="color:red;">ERROR: '.$this->Lang('error_noparentclass').'</h1>';
	return;
  }
require_once($cgextensions);
///////////////////////////////////////////////////////////////////////////

class SMSG extends CGExtensions
{
	const TEST_MESSAGE = 'Test Message from SMSG';
	const PREF_NEWENTERNUMBER_TPL = 'enternumber_newtpl';
	const PREF_DFLTENTERNUMBER_TPL = 'enternumber_dflttpl';
	const PREF_NEWENTERTEXT_TPL = 'entertext_newtpl';
	const PREF_DFLTENTERTEXT_TPL = 'entertext_dflttpl';
	//flags for data-conversion
	const DATA_ASIS = 0;
	const DATA_RAWURL = 1; //rawurlencode
	const DATA_URL = 2; //urlencode
	const DATA_PW = 80; //password

	const ENC_ROUNDS = 10000;
	//whether password encryption is supported
	protected $havemcrypt;

  public function __construct()
  {
	parent::__construct();
	$this->havemcrypt = (function_exists('mcrypt_encrypt'));
	$this->RegisterModulePlugin();
  }

  public function GetName()
  {
	return 'SMSG';
  }

  public function GetFriendlyName()
  {
	return $this->Lang('friendlyname');
  }

  public function GetVersion()
  {
	return '1.2';
  }

  public function GetHelp()
  {
	return $this->Lang('help');
  }

  public function GetAuthor()
  {
	return 'calguy1000';
  }

  public function GetAuthorEmail()
  {
	return 'calguy1000@cmsmadesimple.org';
  }

  public function GetChangeLog()
  {
	$txt = ''.@file_get_contents(cms_join_path(dirname(__FILE__),'include','changelog.inc'));
	return $txt;
  }

  public function IsPluginModule()
  {
	return TRUE;
  }

  public function HasAdmin()
  {
	return TRUE;
  }

  function LazyLoadAdmin()
  {
	return FALSE;
  }

  public function GetAdminSection()
  {
	return 'extensions';
  }

  public function GetAdminDescription()
  {
	return $this->Lang('module_description');
  }

  public function VisibleToAdminUser()
  {
	return
	 $this->CheckPermission('AdministerSMSGateways') ||
	 $this->CheckPermission('ModifySMSGateways') ||
	 $this->CheckPermission('ModifySMSGateTemplates');
  }

  function AdminStyle()
  {
  }

  function GetHeaderHTML()
  {
	$fp = cms_join_path(dirname(__FILE__),'include','module.js');
	$js = ''.@file_get_contents($fp);
	if( $js )
	  {
		$p = ($this->CheckPermission('AdministerSMSGateways')) ? '1':'0';
		$js = str_replace(array('|PADM|','|MAXSMSCHARS|'),array($p,160),$js);
		return
		 '<script type="text/javascript" src="'.$this->GetModuleURLPath().
		 '/include/jquery.tablednd.min.js"></script>'."\n".$js;
	  }
	return '';
  }	

  public function GetDependencies()
  {
	return array('CGExtensions'=>'1.17.7');
  }

  function AllowSmartyCaching()
  {
	return TRUE;
  }

  function LazyLoadFrontend()
  {
	return TRUE;
  }

  public function InstallPostMessage()
  {
	return $this->Lang('postinstall');
  }

  public function MinimumCMSVersion()
  {
	return '1.6.5';
  }

  public function UninstallPostMessage()
  {
	return $this->Lang('postuninstall');
  }

  public function AllowAutoInstall() 
  {
	return FALSE;
  }

  public function AllowAutoUpgrade() 
  {
	return FALSE;
  }

  //setup for pre-1.10
  function SetParameters()
  {
	$this->InitializeAdmin();
	$this->InitializeFrontend();
  }

  //partial setup for pre-1.10, backend setup for 1.10+
  function InitializeFrontend()
  {
	$this->RestrictUnknownParams();
	$this->SetParameterType('action',CLEAN_STRING);
	$this->SetParameterType('destpage',CLEAN_STRING);
	$this->SetParameterType('enternumbertemplate',CLEAN_STRING);
	$this->SetParameterType('entertexttemplate',CLEAN_STRING);
	$this->SetParameterType('inline',CLEAN_INT);
	$this->SetParameterType('linktext',CLEAN_STRING);
	$this->SetParameterType('smskey',CLEAN_STRING); //hash of cached data, for internal use only
	$this->SetParameterType('smsnum',CLEAN_INT);
	$this->SetParameterType('smstext',CLEAN_STRING);
	$this->SetParameterType('urlonly',CLEAN_INT);
	$this->SetParameterType(CLEAN_REGEXP.'/smsg_.*/',CLEAN_NONE);

	$this->RegisterRoute('/SMSG\/devreport$/',array('action'=>'devreport'));
  }

  //partial setup for pre-1.10, backend setup for 1.10+
  function InitializeAdmin()
  {
	$this->CreateParameter('action','enternumber',$this->Lang('help_action'));
	$this->CreateParameter('destpage','0',$this->Lang('help_destpage'));
	$this->CreateParameter('enternumbertemplate','',$this->Lang('help_enternumbertemplate'));
	$this->CreateParameter('entertexttemplate','',$this->Lang('help_enternumbertemplate'));
	$this->CreateParameter('inline',0,$this->Lang('help_inline'));
	$this->CreateParameter('linktext',$this->Lang('send_to_mobile'),$this->Lang('help_linktext'));
	$this->CreateParameter('smsnum',0,$this->Lang('help_smsnum'));
	$this->CreateParameter('smstext','',$this->Lang('help_smstext'));
	$this->CreateParameter('urlonly',0,$this->Lang('help_urlonly'));
  }

} // end of class
#
# EOF
#
?>
