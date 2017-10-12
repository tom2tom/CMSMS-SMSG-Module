<?php
#-------------------------------------------------------------------------
# CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Derived in part from module CGSMS by Robert Campbell <calguy1000@cmsmadesimple.org>
# This module provides the ability for other modules to send SMS messages
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2015 Ted Kulp (wishy@cmsmadesimple.org)
# Its homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This module is free software. You can redistribute and/or modify it
# under the terms of the GNU Affero General Public License as published
# by the Free Software Foundation, either version 3 of that License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
# Read the License online: http://www.gnu.org/licenses/licenses.html#AGPL
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

	public $before20;
	public $oldtemplates;

	public function __construct()
	{
		parent::__construct();
		global $CMS_VERSION;
		$this->before20 = (version_compare($CMS_VERSION,'2.0') < 0);
		$this->oldtemplates = $this->before20 || 1; //TODO
		$this->RegisterModulePlugin(TRUE);

//		spl_autoload_register(array($this,'cmsms_spacedload'));
	}

/*	public function __destruct()
	{
		spl_autoload_unregister(array($this,'cmsms_spacedload'));
		if (function_exists('parent::__destruct'))
			parent::__destruct();
	}
*/
	/* namespace autoloader - CMSMS default autoloader doesn't do spacing */
/*	private function cmsms_spacedload($class)
	{
		$prefix = get_class().'\\'; //our namespace prefix
		$o = ($class[0] != '\\') ? 0:1;
		$p = strpos($class, $prefix, $o);
		if ($p === 0 || ($p == 1 && $o == 1)) {
			// directory for the namespace
			$bp = __DIR__.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
		} else {
			$p = strpos($class, '\\', 1);
			if ($p === FALSE) {
				return;
			}
			$prefix = substr($class, $o, $p-$o);
			$bp = dirname(__DIR__).DIRECTORY_SEPARATOR.$prefix.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
		}
		// relative class name
		$len = strlen($prefix) + $o;
		$relative_class = trim(substr($class, $len), '\\');

		if (($p = strrpos($relative_class,'\\',-1)) !== FALSE) {
			$relative_dir = strtr ($relative_class, '\\', DIRECTORY_SEPARATOR);
			$bp .= substr($relative_dir, 0, $p+1);
			$base = substr($relative_dir, $p+1);
		} else {
			$base = $relative_class;
		}

		$fp = $bp.'class.'.$base.'.php';
		if (file_exists($fp)) {
			include $fp;
			return;
		}
		$fp = $bp.$base.'.php';
		if (file_exists($fp)) {
			include $fp;
		}
	}
*/
	public function AllowAutoInstall()
	{
		return FALSE;
	}

	public function AllowAutoUpgrade()
	{
		return FALSE;
	}

	//for 1.11+
	public function AllowSmartyCaching()
	{
		return TRUE;
	}

	public function GetName()
	{
		return self::MODNAME;
	}

	public function GetFriendlyName()
	{
		return $this->Lang('friendlyname');
	}

	public function GetHelp()
	{
		return ''.@file_get_contents(cms_join_path(__DIR__,'lib','doc','modhelp.htm'));
	}

	public function GetVersion()
	{
		return '1.2.1';
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
		return ''.@file_get_contents(cms_join_path(__DIR__,'lib','doc','changelog.htm'));
	}

	public function IsPluginModule()
	{
		return TRUE;
	}

	public function HasCapability($capability,$params = [])
	{
		switch($capability) {
		 case 'plugin':
		 case 'tasks':
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

	public function AdminStyle()
	{
		$fn = cms_join_path(dirname(__FILE__),'css','admin.css');
		return ''.@file_get_contents($fn);
	}

/*	public function GetHeaderHTML()
	{
	}
*/
	public function GetDependencies()
	{
		return [];
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
		switch($eventname) {
		 case 'SMSDeliveryReported':
			return $this->Lang('event_desc_delivery');
		 default:
			return '';
		}
	}

	public function GetEventHelp($eventname)
	{
		switch($eventname) {
		 case 'SMSDeliveryReported':
			return $this->Lang('event_help_delivery');
		 default:
			return '';
		}
	}

	public function get_tasks()
	{
		if ($this->before20) {
			return new smsgClearlogTask();
		} else {
			return new SMSG\ClearlogTask();
		}
	}
}
