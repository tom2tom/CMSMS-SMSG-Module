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
# This file is part of an addon module for CMS Made Simple.
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

//class name must be like 'somename_sms_gateway'
class skeleton_sms_gateway extends smsg_sender_base
{
	//TODO specific name and real URL for API reference
	const SKEL_API_URL = 'https://somewhere.com/...';
	private $_rawstatus;

	public function upsert_tables()
	{
		$module = parent::get_module();
		$gid = smsg_utils::setgate($module,$this,SMSG::DATA_ASIS);
	    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=apiconvert
		//by convention, apiname's which are not actually used are indicated by a '_' prefix
		//TODO
		if($gid) smsg_utils::setprops($module,$gid,array(
			array($module->Lang('username'),'user',NULL,SMSG::DATA_ASIS),
			array($module->Lang('password'),'password',NULL,SMSG::DATA_PW)
			));
		return $gid;
	}

	public function custom_setup(&$smarty,$padm)
	{
		//TODO e.g.
		foreach($smarty->tpl_vars['data']->value as &$ob)
		{
		}
		unset($ob)
		if($padm)
		{
			$mod = parent::get_module();
			$help = $smarty->tpl_vars['help']->value.'<br />'.
			 $mod->Lang('help_urlcheck',self::SKEL_API_URL,self::get_name().' API');
			$smarty->assign('help',$help);
		}
	}

	public function custom_save(&$params)
	{
		//TODO
	}

	public function get_name()
	{
		//TODO
		return 'My Name';
	}

	public function get_alias()
	{
		//must be this class' name less the trailing '_sms_gateway'
		//TODO
		return 'skeleton';
	}

	public function get_description()
	{
		//DEPRECATED see database table
		return '';
	}

	public function support_custom_sender()
	{
		//TODO
		return FALSE;
	}

	public function support_mms()
	{
		//TODO
		return FALSE;
	}

	public function require_country_prefix()
	{
		//TODO
		return TRUE;
	}

	public function require_plus_prefix()
	{
		return FALSE;
	}

	public function multi_number_separator()
	{
		//TODO
		return FALSE;
	}

	protected function setup()
	{
		//TODO
	}

	protected function prep_command()
	{
		//TODO
		return $str;
	}

	protected function parse_result($str)
	{
		$this->_rawstatus = $str;
		//TODO
		$this->set_status(self::STAT_ERROR_AUTH); //or whatever
	}

	public function process_delivery_report()
	{
		//TODO
	    $smsto = '';
		$smsid = '';
		$status = smsg_sender_base::DELIVERY_UNKNOWN; //or whatever
		return smsg_utils::get_delivery_msg($this,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
}

?>
