<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

//class name must be like 'somename_sms_gateway'
class skeleton_sms_gateway extends base_sms_gateway
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
			parent::set_gateid($gid);
			$mod = $this->_module;
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			//by convention, apiname's which are not actually used are indicated by a '_' prefix
			smsg_utils::setprops($gid,array(
			 array($mod->Lang('username'),'user',NULL,0),
			 array($mod->Lang('password'),'password',NULL,1)
			));
		}
		return $gid;
	}

	public function custom_setup(&$tplvars,$padm)
	{
		//e.g.
		foreach($tplvars['data'] as &$ob)
		{
			//set stuff e.g. $ob->size, $ob->help
		}
		unset($ob);
		if($padm)
		{
			$tplvars['help'] .= '<br />'.
				$this->_module->Lang('help_urlcheck',self::SKEL_API_URL,self::get_name().' API');
		}
	}

	public function custom_save(&$params)
	{
		//TODO
/* $params = array like (
  'sms_gateway' => 'skeleton'
  ....
  'skeleton~user~title' => 'Username'
  'skeleton~user~value' => 'Me'
  'skeleton~user~apiname' => 'user'
  'skeleton~user~active' => 'on'
  'skeleton~password~title' => 'Password'
  'skeleton~password~value' => 'asdasda'
  'skeleton~password~apiname' => 'password'
  'skeleton~password~active' => 'on'
  ....
  'skeleton~gate_id' => string '1')
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
		$parms = smsg_utils::getprops($this->_module,$gid);
		if(
		 $parms['whatever']['value'] == FALSE ||
		 $parms['someother']['value'] == FALSE
		)
		{
			$this->_status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}
		//convert $parms data format if needed
		//MORE $parms - to, from, body etc, format-adjusted as needed
		$str = smsg_utils::implode_with_key($parms);
		$str = some_url.'?'.str_replace('amp;','',$str);
		return $str;
	}

/* if we can't use the internal mechanism to send messages, populate this instead of prep_command()
   and make prep_command { return 'good'; //or anything else not FALSE }
	protected function _command($cmd)
	{
	}
*/
	protected function parse_result($str)
	{
		$this->_rawstatus = $str;
		//TODO parse $str
		$this->_status = parent::STAT_ERROR_AUTH; //or whatever
	}

	public function process_delivery_report()
	{
		//TODO must parse $_REQUEST directly
		$smsto = '';
		$smsid = '';
		$status = parent::DELIVERY_UNKNOWN; //or whatever
		return smsg_utils::get_delivery_msg($this->_module,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
}

?>
