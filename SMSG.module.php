<?php
#-------------------------------------------------------------------------
# CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Derived in part from module CGSMS by Robert Campbell <calguy1000@cmsmadesimple.org>
# This module provides the ability for other modules to send SMS messages
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2015 Ted Kulp (wishy@cmsmadesimple.org)
# Its homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This module is free software; you can redistribute and/or modify it
# under the terms of the GNU Affero General Public License as published
# by the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
# Read the Licence online: http://www.gnu.org/licenses/licenses.html#AGPL
#-------------------------------------------------------------------------

class SMSG extends CMSModule
{
	const MODNAME = 'SMSG';
	const AUDIT_SEND = 1;
	const AUDIT_DELIV = 2;
	const AUDIT_ERR = 3;
	//these are all like prefix.base
	const PREF_ENTERNUMBER_TPLDFLT = 'enternumber_dflttpl';
	const PREF_ENTERNUMBER_CONTENTDFLT = 'enternumber_defaultcontent';
	const PREF_ENTERTEXT_TPLDFLT = 'entertext_dflttpl';
	const PREF_ENTERTEXT_CONTENTDFLT = 'entertext_defaultcontent';

	const ENC_ROUNDS = 10000;
	//whether password encryption is supported
	public $havemcrypt;
	public $before20;

	public function __construct()
	{
		parent::__construct();
		$this->havemcrypt = (function_exists('mcrypt_encrypt'));
		global $CMS_VERSION;
		$this->before20 = (version_compare($CMS_VERSION,'2.0') < 0);
		$this->RegisterModulePlugin(TRUE);
	}

	public function AllowAutoInstall()
	{
		return FALSE;
	}

	public function AllowAutoUpgrade()
	{
		return FALSE;
	}

	public function GetName()
	{
		return self::MODNAME;
	}

	public function GetFriendlyName()
	{
		return $this->Lang('friendlyname');
	}

	public function GetVersion()
	{
		return '1.0';
	}

	public function GetHelp()
	{
		return $this->Lang('help_module');
	}

	public function GetAuthor()
	{
		return 'tomphantoo';
	}

	public function GetAuthorEmail()
	{
		return 'tpgww@onepost.net';
	}

	public function GetChangeLog()
	{
		return ''.@file_get_contents(cms_join_path(dirname(__FILE__),'include','changelog.inc'));
	}

	public function IsPluginModule()
	{
		return TRUE;
	}

	public function HasCapability($capability,$params = array())
	{
		switch($capability)
		{
		 case 'SMSgateway':
		 case 'SMSmessaging':
		 case 'SMSG':
		 case 'CGSMS':
			return TRUE;
		 default:
			return FALSE;
		}
	}

	public function HasAdmin()
	{
		return TRUE;
	}

	public function LazyLoadAdmin()
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
		 $this->CheckPermission('ModifySMSGateTemplates') ||
		 $this->CheckPermission('UseSMSGateways');
	}

/*	public function AdminStyle()
	{
	}

	public function GetHeaderHTML()
	{
	}
*/
	public function GetDependencies()
	{
		return array();
	}

	//for 1.11+
	public function AllowSmartyCaching()
	{
		return TRUE;
	}

	public function LazyLoadFrontend()
	{
		return FALSE;
	}

	public function MinimumCMSVersion()
	{
		return '1.9';
	}

/*	public function MaximumCMSVersion()
	{
	}
*/

	public function InstallPostMessage()
	{
		return $this->Lang('postinstall');
	}

	public function UninstallPreMessage()
	{
		return $this->Lang('confirm_uninstall');
	}

	public function UninstallPostMessage()
	{
		return $this->Lang('postuninstall');
	}

	//setup for pre-1.10
	public function SetParameters()
	{
		$this->InitializeAdmin();
		$this->InitializeFrontend();
	}

	//partial setup for pre-1.10, backend setup for 1.10+
	public function InitializeFrontend()
	{
		$this->RestrictUnknownParams();
		$this->SetParameterType('action',CLEAN_STRING);
		$this->SetParameterType('destpage',CLEAN_STRING);
		$this->SetParameterType('enternumbertemplate',CLEAN_STRING);
		$this->SetParameterType('entertexttemplate',CLEAN_STRING);
		$this->SetParameterType('gatename',CLEAN_STRING);
		$this->SetParameterType('inline',CLEAN_INT);
		$this->SetParameterType('linktext',CLEAN_STRING);
		$this->SetParameterType('smskey',CLEAN_STRING); //hash of cached data, for internal use only
		$this->SetParameterType('smsnum',CLEAN_INT);
		$this->SetParameterType('smstext',CLEAN_STRING);
		$this->SetParameterType('urlonly',CLEAN_INT);
		$this->SetParameterType(CLEAN_REGEXP.'/smsg_.*/',CLEAN_NONE);

		$returnid = cmsms()->GetContentOperations()->GetDefaultPageID(); //any valid id will do ?
		$this->RegisterRoute('/SMSG\/devreport$/',
		  array('action'=>'devreport',
				'showtemplate'=>'false', //not FALSE, or any of its equivalents !
				'returnid'=>$returnid));
	}

	//partial setup for pre-1.10, backend setup for 1.10+
	public function InitializeAdmin()
	{
		$this->CreateParameter('action','enternumber',$this->Lang('help_action'));
		$this->CreateParameter('destpage','0',$this->Lang('help_destpage'));
		$this->CreateParameter('enternumbertemplate','',$this->Lang('help_enternumbertemplate'));
		$this->CreateParameter('entertexttemplate','',$this->Lang('help_enternumbertemplate'));
		$this->CreateParameter('gatename','',$this->Lang('help_gatename'));
		$this->CreateParameter('inline',0,$this->Lang('help_inline'));
		$this->CreateParameter('linktext',$this->Lang('send_to_mobile'),$this->Lang('help_linktext'));
		$this->CreateParameter('smsnum',0,$this->Lang('help_smsnum'));
		$this->CreateParameter('smstext','',$this->Lang('help_smstext'));
		$this->CreateParameter('urlonly',0,$this->Lang('help_urlonly'));
	}

	public function GetEventDescription($eventname)
	{
		switch($eventname)
		{
		 case 'SMSDeliveryReported':
			return $this->Lang('event_desc_delivery');
		 default:
			return '';
		}
	}

	public function GetEventHelp($eventname) 
	{
		switch($eventname)
		{
		 case 'SMSDeliveryReported':
			return $this->Lang('event_help_delivery');
		 default:
			return '';
		}
	}

	public function get_tasks()
	{
		return new smsg_clearlog_task();
	}

	//construct delivery-reports URL (pretty or not)
	public function get_reporturl()
	{
		$returnid = cmsms()->GetContentOperations()->GetDefaultContent();
		//CMSMS 1.10+ has ->create_url();
		return $this->CreateLink('m1_','devreport',$returnid,'',array(),'',
			TRUE,FALSE,'',FALSE,'SMSG/devreport');
	}

}

?>
