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
class googlevoice_sms_gateway extends cgsms_sender_base
{
  private $_rawstatus;

  public function get_name()
  {
    return 'Google Voice';
  }

  public function get_description()
  {
    return $this->get_module()->Lang('description_googlevoice');
  }

  public function support_custom_sender()
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

  public function get_setup_form()
  {
    global $gCms;
    $smarty = $gCms->GetSmarty();
    $mod = $this->get_module();

    $smarty->assign('googlevoice_email',$mod->GetPreference('googlevoice_email'));
    $smarty->assign('googlevoice_password',$mod->GetPreference('googlevoice_password'));

    return $mod->ProcessTemplate('googlevoice_setup.tpl');
  }

  public function handle_setup_form($params)
  {
    $mod = $this->get_module();
    if( isset($params['googlevoice_email']) )
      {
	$mod->SetPreference('googlevoice_email',trim($params['googlevoice_email']));
      }
    if( isset($params['googlevoice_password']) )
      {
	$mod->SetPreference('googlevoice_password',trim($params['googlevoice_password']));
      }
  }

  protected function setup()
  {
    // nothing to do here.
  }

  protected function prep_command()
  {
    // need to return something. even though we ignore it.
    return 'good';
  }

  protected function _command($cmd)
  {
    try {
      $mod = $this->get_module();
      require_once(dirname(__FILE__).'/googlevoice/class.googlevoice2.php');
      $gv = new GoogleVoice($mod->GetPreference('googlevoice_email'),
			    $mod->GetPreference('googlevoice_password'));
      
      $num = $this->get_num();
      $num = preg_replace('/[^\d]/','',$num);

      $msg = substr(strip_tags($this->get_msg()),0,160);
      $gv->sms($num,$msg);
      
      // need to return a status;
      return 'good';
    }
    catch(Exception $e)
      {
	die('got exception '.$e->getMessage());
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

  public function _process_delivery_report()
  {
    // nothing to do here.
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
