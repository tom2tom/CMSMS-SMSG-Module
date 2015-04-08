<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

//class name must be like 'somename_sms_gateway'
class skeleton_sms_gateway extends smsg_sender_base
{
	//TODO specific name and real URL for API reference
	const SKEL_API_URL = 'https://somewhere.com/...';
	private $_rawstatus;

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

	public function upsert_tables()
	{
		$gid = smsg_utils::setgate($this);
		if($gid)
		{
			$module = parent::get_module();
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			//by convention, apiname's which are not actually used are indicated by a '_' prefix
			smsg_utils::setprops($gid,array(
			array($module->Lang('username'),'user',NULL,0),
			array($module->Lang('password'),'password',NULL,1)
			));
		}
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
/* $params = array like (
  'sms_gateway' => string 'clickatell' (length=10)
  'clickatell~user~title' => string 'Username' (length=8)
  'clickatell~user~value' => string 'M' (length=1)
  'clickatell~user~apiname' => string 'user' (length=4)
  'clickatell~user~active' => string 'on' (length=2)
  'clickatell~password~title' => string 'Password' (length=8)
  'clickatell~password~value' => string 'asdasda' (length=7)
  'clickatell~password~apiname' => string 'password' (length=8)
  'clickatell~password~active' => string 'on' (length=2)
  'clickatell~api_id~title' => string 'API ID' (length=6)
  'clickatell~api_id~value' => string '393939' (length=6)
  'clickatell~api_id~apiname' => string 'api_id' (length=6)
  'clickatell~gate_id' => string '1' (length=1)
  ....
*/
	}

	protected function setup()
	{
		//TODO
	}

	protected function prep_command()
	{
		//get 'public' parameters for interface
		$gid = parent::get_gateid(self::get_alias());
		$parms = smsg_utils::getprops($gid);
		//TODO maybe some valid empty parm
		if(in_array(FALSE,$parms))
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		//convert $parms data format if needed
		//MORE $parms - to, from, body etc, format-adjusted as needed
		$str = cge_array::implode_with_key($parms);
		$str = some_url.'?'.str_replace('amp;','',$str);
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
