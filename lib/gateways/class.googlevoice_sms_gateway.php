<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class googlevoice_sms_gateway extends sms_gateway_base
{
  const GOOGLEVOICE_API_URL = 'https://code.google.com/p/phpgooglevoice';
  private $_rawstatus;

  public function get_name()
  {
    return 'Google Voice';
  }

  public function get_alias()
  {
    return 'googlevoice';
  }

  public function get_description()
  {
    return $this->_module->Lang('description_googlevoice');
  }

  public function support_custom_sender()
  {
    return FALSE; //TODO
  }

  public function support_mms()
  {
    return FALSE; //TODO
  }

  public function require_country_prefix()
  {
    return TRUE; //TODO
  }

  public function require_plus_prefix()
  {
    return FALSE;
  }

  public function multi_number_separator()
  {
    return FALSE; //TODO
  }

  public function upsert_tables()
  {
	$gid = smsg_utils::setgate($this);
	if($gid)
	  {
		parent::set_gateid($gid);
		$module = $this->_module;
		//setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
		//none of the apiname's is actually used (indicated by '_' prefix)
		smsg_utils::setprops($gid,array(
		 array($module->Lang('email'),'_email',NULL,0),
		 array($module->Lang('password'),'_password',NULL,1)
		));
	  }
	return $gid;
  }

  public function custom_setup(&$smarty,$padm)
  {
    foreach($smarty->tpl_vars['data']->value as &$ob)
      {
        if($ob->signature == '_email')
          $ob->size = 24;
		elseif($ob->signature == '_password')
          $ob->size = 20;
      }
    unset($ob);
    if($padm)
      {
        $help = $smarty->tpl_vars['help']->value.'<br />'.
         $this->_module->Lang('help_urlcheck',self::GOOGLEVOICE_API_URL,self::get_name().' API');
        $smarty->assign('help',$help);
      }
  }

  public function custom_save(&$params)
  {
  }

  protected function setup()
  {
	require_once(cms_join_path(dirname(__FILE__),'googlevoice','class.googlevoice2.php'));
  }

  protected function prep_command()
  {
    // need to return something. even though we ignore it
    return 'good';
  }

  protected function _command($cmd)
  {
	try
	  {
		$gid = parent::get_gateid(self::get_alias());
		$parms = smsg_utils::getprops($this->_module,$gid);
		$gv = new GoogleVoice(
		$parms['_email']['value'],
		$parms['_password']['value']);

		$num = $this->_num;
		if( !$num )
			return FALSE;
		$msg = strip_tags($this->_msg);
		if( !self::support_mms() )
			$msg = substr($msg,0,160);
		if( !smsg_utils::text_is_valid($msg,0) )
			return FALSE;
		$gv->sms($num,$msg); //result ignored
		// need to return a status
		return 'good';
	  }
	catch(Exception $e)
	  {
		return $e->getMessage();
	  }
  }

  protected function parse_result($str)
  {
    $this->_rawstatus = $str;
    if( $str == 'good' )
		$this->_status = parent::STAT_OK;
	elseif( $str === FALSE )
		$this->_status = parent::STAT_ERROR_INVALID_DATA;
	else
		$this->_status = parent::STAT_ERROR_OTHER;
  }

  public function process_delivery_report()
  {
    return ''; //nothing to report here
  }

  public function get_raw_status()
  {
    return $this->_rawstatus;
  }
} // end of class

?>
