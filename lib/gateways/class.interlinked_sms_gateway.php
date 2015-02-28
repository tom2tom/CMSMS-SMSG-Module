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
class interlinked_sms_gateway extends cgsms_sender_base
{
  const INTERLINKED_HTTP_GATEWAY = 'http://my.Interlinked.mobi/send.ashx';
  private $_rawstatus;

  public function get_name()
  {
    return 'Interlinked.mobi';
  }

  public function get_description()
  {
    return $this->get_module()->Lang('description_interlinked');
  }

  public function get_setup_form()
  {
    global $gCms;
    $smarty = $gCms->GetSmarty();
    $mod = $this->get_module();

    $smarty->assign('interlinked_username', $mod->GetPreference('interlinked_username'));
    $smarty->assign('interlinked_password', $mod->GetPreference('interlinked_password'));
    $smarty->assign('interlinked_from',     $mod->GetPreference('interlinked_from'));
    $smarty->assign('interlinked_custom',   $mod->GetPreference('interlinked_custom'));

    return $mod->ProcessTemplate('interlinked_setup.tpl');
  }


  public function handle_setup_form($params)
  {
    $mod = $this->get_module();
    if( isset($params['interlinked_username']) )
      {
	$mod->SetPreference('interlinked_username',trim($params['interlinked_username']));
      }
    if( isset($params['interlinked_password']) )
      {
	$mod->SetPreference('interlinked_password',trim($params['interlinked_password']));
      }
    if( isset($params['interlinked_from']) )
      {
	$mod->SetPreference('interlinked_from',trim($params['interlinked_from']));
      }
    if( isset($params['interlinked_custom']) )
      {
	$mod->SetPreference('interlinked_custom',trim($params['interlinked_custom']));
      }
  }


  protected function setup()
  {
    // nothing to do here.
  }


  protected function prep_command()
  {
    $module = $this->get_module();
    $parms = array();
    $parms['user'] = $module->GetPreference('interlinked_username');
    if( $parms['user'] == '' ) return FALSE;

    $parms['pass'] = $module->GetPreference('interlinked_password');
    if( $parms['user'] == '' ) return FALSE;

    $parms['smsto'] = $this->get_num();
    if( $parms['smsto'] == '' ) return FALSE;
    $parms['smsto'] = preg_replace('/[^\d]/','',$parms['smsto']);

    $parms['smsmsg'] = substr(urlencode(strip_tags($this->get_msg())),0,160);
    if( $parms['smsmsg']  == '' ) return FALSE;

    $from = $module->GetPreference('interlinked_from');
    if( !empty($from) )
      {
	$parms['smsfrom'] = urlencode($from);
      }
    $custom = $module->GetPreference('interlinked_custom');
    if( !empty($custom) )
      {
	$parms['custom'] = urlencode($custom);
      }
    // todo, we could put delay stuff in here too
    $str = self::INTERLINKED_HTTP_GATEWAY.'?'.cge_array::implode_with_key($parms);
    debug_to_log('cmd_before = '.$str);
    $str = str_replace('amp;','',$str);
    debug_to_log('cmd_after = '.$str);
    return $str;
  }


  protected function parse_result($str)
  {
    $this->_rawstatus = $str;
    switch($str)
      {
      case '101':
      case '102':
      case '103':
	$this->set_status(self::STAT_ERROR_AUTH);
	break;

      case '104':
	$this->set_status(self::STAT_ERROR_LIMIT);
	break;

      case '111':
      case '112':
      case '113':
      case '114':
      case '115':
      case '116':
	$this->set_status(self::STAT_ERROR_INVALID_DATA);
	break;

      case '201':
	$this->set_status(self::STAT_ERROR_INVALID_BLOCKED);
	break;

      case '105':
	$this->set_status(self::STAT_ERROR_OTHER);
	break;

      default:
	if( strlen($str) == 9 )
	  {
	    // assume a sussesfull SMS id.
	    $this->set_status(self::STAT_OK);
	    $this->_smsid = $str;
	  }
	else
	  {
	    $this->set_status(self::STAT_ERROR_OTHER);
	  }
	break;
      }
  }


  public function _process_delivery_report()
  {
    debug_to_log('process_delivery_report');
    debug_to_log($_REQUEST);

    // a function to gather the data directly from the REQUEST and handle delivery information.
    if( !isset($_REQUEST['SMSFrom']) || !isset($_REQUEST['SMSID']) || !isset($_REQUEST['STATUS']))
      {
	return;
      }

    // now we could look up the sms message in the database and try to do something
    // but for now we'll just send a log message.
    $smsto = trim($_REQUEST['SMSFrom']);
    $smsid = trim($_REQUEST['SMSID']);
    $status = cgsms_sender_base::DELIVERY_UNKNOWN;
    switch( (int)$_REQUEST['STATUS'] )
      {
      case 0: // in progress.
	// ignore this one.
	$status = self::DELIVERY_PENDING;
	break;

      case 100: // delivered.
	$status = self::DELIVERY_OK;
	break;

      case 200: // invalid/barred
      case 201: // DNS List
      case 351: // Parental block
      case 352: // User barred
	$status = self::DELIVERY_BARRED;
	break;

      case 302: // billing failed
      case 303: // prepay billing unsupported
      case 350: // MT charge barred
	$status = self::DELIVERY_BILLING_ERROR;
	break;

      case 353: // SIM Full
      case 354: // Absent
      case 355: // Filtered
      case 356: // Msg Expired
	$status = self::DELIVERY_OTHER;
	break;

      default:  // unknown
	debug_to_log('CGSMS::interlinked gateway: unknown error code '.$_REQUEST['STATUS'].' in delivery report');
	break;  
      }

    return cgsms_utils::get_delivery_msg($this,$status,$smsid,$smsto);
  }

  public function get_raw_status()
  {
    return $this->_rawstatus;
  }
} // end of class

#
# EOF
#
?>