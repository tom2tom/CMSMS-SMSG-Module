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

class googlevoice_sms_gateway extends smsg_sender_base
{
  const GOOGLEVOICE_API_URL = ''; 
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
    return $this->get_module()->Lang('description_googlevoice');
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
    return FALSE; //TODO
  }

  public function require_plus_prefix()
  {
    return FALSE; //TODO
  }

  public function multi_number_separator()
  {
    return FALSE; //TODO
  }

  public function upsert_tables()
  {
		$module = parent::get_module();
		$gid = smsg_utils::setgate($module,$this,SMSG::DATA_ASIS);
	    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=apiconvert
		//none of the apiname's is actually used (indicated by '_' prefix)
		if($gid) smsg_utils::setprops($module,$gid,array(
			array($module->Lang('email'),'_email',NULL,SMSG::DATA_ASIS),
			array($module->Lang('password'),'_password',NULL,SMSG::DATA_PW)
			));
		return $gid;
  }

  public function custom_setup(&$smarty,$padm)
  {
    foreach($smarty->tpl_vars['data']->value as &$ob)
      {
        if($ob->apiname == '_email')
          $ob->size = 24;
        elseif(!empty($ob->pass))
          $ob->size = 20;
      }
    unset($ob);
    if($padm)
      {
        $mod = parent::get_module();
        $help = $smarty->tpl_vars['help']->value.'<br />'.
         $mod->Lang('help_urlcheck',self::GOOGLEVOICE_API_URL,self::get_name().' API');
        $smarty->assign('help',$help);
      }
  }

  public function custom_save(&$params)
  {
  }

  protected function setup()
  {
  }

  protected function prep_command()
  {
    // need to return something. even though we ignore it.
    return 'good';
  }

  protected function _command($cmd)
  {
    try {
      $mod = parent::get_module();
      require_once(cms_join_path(dirname(__FILE__),'googlevoice','class.googlevoice2.php'));
      $gv = new GoogleVoice($mod->GetPreference('googlevoice_email'),
             $mod->GetPreference('googlevoice_password'));

      $num = $this->get_num();
      $num = preg_replace('/[^\d]/','',$num);

      $msg = substr(strip_tags($this->get_msg()),0,160);
      $gv->sms($num,$msg); //result ignored

      // need to return a status;
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
    if( $str != 'good' )
      {
        $this->set_status(self::STAT_ERROR_OTHER);
      }
    $this->set_status(self::STAT_OK);
  }

  public function process_delivery_report()
  {
    return ''; //nothing to do here
  }

  public function get_raw_status()
  {
    return $this->_rawstatus;
  }
}


#
# EOF
#
?>
