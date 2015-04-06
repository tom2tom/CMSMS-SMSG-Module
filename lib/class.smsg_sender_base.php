<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

abstract class smsg_sender_base
{
	const STAT_OK = 'sms_sent';
	const STAT_NOTSENT = 'sms_notsent';
	const STAT_ERROR_OTHER = 'sms_error_other';
	const STAT_ERROR_AUTH = 'sms_error_auth';
	const STAT_ERROR_LIMIT = 'sms_error_limit';
	const STAT_ERROR_INVALID_DATA = 'sms_error_invalid_data';
	const STAT_ERROR_BLOCKED = 'sms_error_blocked_number';
	//CHECKME these may be used as Lang() keys
	//see <gateway>_sms_gateway::process_delivery_report() and smsg_utils::get_delivery_msg()
	const DELIVERY_OK = 'sms_delivery_ok';
	const DELIVERY_PENDING = 'sms_delivery_pending';
	const DELIVERY_INVALID = 'sms_delivery_invalid';
	const DELIVERY_UNKNOWN = 'sms_delivery_unknown';
	const DELIVERY_BILLING = 'sms_delivery_billing';
	const DELIVERY_OTHER   = 'sms_delivery_other';

	private $_module;
	private $_gate_id;
	private $_num;
	private $_fromnum;
	private $_msg;
	private $_statusmsg;
	protected $_use_curl;
	protected $_status;
	protected $_smsid;

  function __construct(&$module)
  {
	$this->_module = $module;
  	$this->_gate_id = 0;
	self::reset();
  }

  protected function get_module()
  {
	return $this->_module;
  }

  protected function set_gateid($gid)
  {
	$this->_gate_id = (int)$gid;
  }

  protected function get_gateid($alias,$force = FALSE)
  {
	if($force || !$this->_gate_id)
	  {
		$db = cmsms()->GetDb();
		$query = 'SELECT gate_id FROM '.cms_db_prefix().'module_smsg_gates WHERE alias=?';
		$gid = $db->GetOne($query,array($alias));
		$this->_gate_id = (int)$gid;
	  }
	return $this->_gate_id;
  }

  /**
  reset:
  Clear all cached data
  */
  public function reset()
  {
	$this->_num = '';
	$this->_fromnum = '';
	$this->_msg = '';
	$this->_use_curl = 0;
	$this->_status = self::STAT_NOTSENT;
	$this->_statusmsg = '';
  }

  public function use_curl($flag = TRUE)
  {
	$this->_use_curl = ( $flag ) ? 1 : 0;
  }

  /**
  set_msg:
  @msg: the body-content of the message to be sent,including only SMS-valid
   characters,and of suitable length
  */
  public function set_msg($msg)
  {
	$this->_msg = $msg;
  }


  protected function get_msg()
  {
	return $this->_msg;
  }

  /**
  set_num:
  @num: destination phone-number(or separated numbers,if the gateway supports
   batching) appropriately formatted for the gateway
  */
  public function set_num($num)
  {
	$this->_num = $num;
  }


  protected function get_num()
  {
	return $this->_num;
  }

  /**
  set_from:
  @from: the source phone-number(if the gateway supports that) appropriately formatted
  */
  public function set_from($from)
  {
	$this->_fromnum = ( $from ) ? $from : FALSE;
  }

  protected function get_from()
  {
	return $this->_fromnum;
  }

  protected function set_status($stat)
  {
	$this->_status = $stat;
  }

  /**
  get_status:
  Returns string describing(short-form) status,one of the const's defined above
  */
  public function get_status()
  {
	return $this->_status;
  }

  /**
  get_statusmsg:
  Returns string ...
  */
  public function get_statusmsg()
  {
	return $this->_statusmsg;
  }

  /**
  send:
  Initiate the message transmission(after all relevant parameters are set up)
  */
  public function send()
  {
	$this->_smsid = '';

	// check to make sure we have necessary data
	$this->setup();
	if( $this->_num == '' || $this->_msg == '' )
	  {
		$this->_status = self::STAT_ERROR_INVALID_DATA;
		return FALSE;
	  }

	if( !smsg_utils::ip_can_send(getenv('REMOTE_ADDR')) )
	  {
		$this->_status = self::STAT_ERROR_LIMIT;
		return FALSE;
	  }

	// next prepare the output
	$cmd = $this->prep_command();
	if( $cmd === FALSE || $cmd == '' )
	  {
		$this->_status = self::STAT_ERROR_INVALID_DATA;
		return FALSE;
	  }

	// send it
	$res = $this->_command($cmd);

	// interpret result
	$this->parse_result($res);
	$this->_statusmsg = smsg_utils::get_msg($this,$this->_num,$this->_status,$this->_msg,$this->get_raw_status());
	$success = ($this->_status == self::STAT_OK);
	if( $success )
	  {
		if( $this->_module->GetPreference('logsends') )
			smsg_utils::log_send(getenv('REMOTE_ADDR'),$this->_num,$this->_msg);
		$this->_module->Audit('',$this->_module->GetName(),$this->_statusmsg);
	  }
	return $success;
  }

  //for internal use only
  //get parameter stored(for some gateways) when operation result-message was parsed
  public function get_smsid()
  {
	return $this->_smsid;
  }

  //performs the send,may need to be over-ridden in some gateway-specific subclasses
  protected function _command($cmd)
  {
	$this->_check_curl();
	$res = '';
	$res = ( $this->_use_curl == 0 ) ?
		$this->_send_fopen($cmd):
		$this->_send_curl($cmd);
	return $res;
  }

  private function _send_fopen($cmd)
  {
	$res = '';
	$fh = @fopen($cmd,'r');
	if( $fh )
	  {
		while( $line = @fgets($fh,1024) ) $res .= $line;
		fclose($fh);
		return $res;
	  }
	else
		return FALSE;
  }

  private function _send_curl($cmd)
  {
	$ch = curl_init($cmd);
	curl_setopt($ch,CURLOPT_HEADER,0);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
/*	if( $this->curl_use_proxy )
	  {
		curl_setopt($ch,CURLOPT_PROXY,$this->curl_proxy);
		curl_setopt($ch,CURLOPT_PROXYUSERPWD,$this->curl_proxyuserpwd);
	  }
*/
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
  }

  private function _check_curl()
  {
	if( $this->_use_curl == FALSE )
	  {
		if( extension_loaded('curl') )
			$this->_use_curl = 1;
	  }
  }

  /**
  get_setup_form:
  Returns string,xhtml for echo into admin display
  */
  public function get_setup_form()
  {
	$module = $this->_module;
	$padm = $module->CheckPermission('AdministerSMSGateways');
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();
	$query = 'SELECT * FROM '.$pref.'module_smsg_gates WHERE alias=?';
	if( !$padm )
		$query .= ' AND enabled=1';
	$alias = $this->get_alias();
	$gdata = $db->GetRow($query,array($alias));
	if(!$gdata)
		return '';

	$smarty = cmsms()->GetSmarty();
	$smarty->assign('gatetitle',$module->Lang('frame_title',$gdata['title']));
	$parms = array();
	$query = 'SELECT gate_id,title,value,apiname,apiconvert,active FROM '.$pref.'module_smsg_props WHERE gate_id=?';
	if( !$padm )
		$query .= ' AND active=1';
	$query .= ' ORDER BY apiorder';
	$gid = (int)$gdata['gate_id'];
	$res = $db->GetAll($query,array($gid));
	if( $res )
	  {
		foreach( $res as &$row )
		  {
			$ob = (object)$row;
			//adjustments
			if((int)$ob->apiconvert >= SMSG::DATA_PW ) //parameter is password
			  {
				$ob->value = smsg_utils::decrypt_value($ob->value);
				$ob->pass = TRUE; //signal to 'other users' this is a password
			  }
			unset($ob->apiconvert);
			$parms[] = $ob;
		  }
		unset($row);
	  }
	$dcount = count($parms);
	if($dcount == 0)
	  {
		$ob = new stdClass();
		$ob->title = $module->Lang('error_nodatafound');
		$ob->value = '';
		$ob->apiname = FALSE; //prevent input-object creation
		$parms[] = $ob;
	  }
	$smarty->assign('data',$parms);
	$smarty->assign('dcount',$dcount);
	$smarty->assign('space',$alias); //for gateway-data 'namespace'
	$smarty->assign('gateid',$gid);
	if( $padm )
	  {
		$smarty->assign('titletitle',$module->Lang('title'));
		$smarty->assign('titlevalue',$module->Lang('value'));
		$smarty->assign('titleapiname',$module->Lang('apiname'));
		$smarty->assign('titleactive',$module->Lang('enabled'));
		$smarty->assign('titlehelp',$module->Lang('helptitle'));
		$smarty->assign('titleselect',$module->Lang('select'));
		$smarty->assign('help',
		 $module->Lang('help_dnd').'<br />'.$module->Lang('help_sure'));
		$id = $smarty->tpl_vars['actionid']->value;
		$text = $module->Lang('add_parameter');
		$smarty->assign('additem',$module->CreateImageLink($id,'addsetting',
		 '',$text,'icons/system/newobject.gif',array('gate_id'=>$gid),'systemicon','',FALSE));
		if( $dcount > 0 )
			$smarty->assign('btndelete',$module->CreateInputSubmit($id,$alias.'~delete',
			 $module->Lang('delete'),'title="'.$module->Lang('delete_tip').
			 '" onclick="if(row_selected(event,this)) {return confirm(\''.$module->Lang('sure_ask').'\');} else {return false;}"'));
	  }
	// anything else to set up for the template
	$this->custom_setup($smarty,$padm); //e.g. each $ob->size

	$tpl = ($padm) ? 'gatedata_admin.tpl' : 'gatedata.tpl';
	return $module->ProcessTemplate($tpl);
  }

  /**
  handle_setup_form:
  @params: array of paramters provided after admin form 'submit'
  Parses relevant @params into stored data, or deletes stored data if so instructed
  */
  public function handle_setup_form($params)
  {
	$alias = $this->get_alias();
	$db = cmsms()->GetDb();
	$pref = cms_db_prefix();

	$gid = (int)$params[$alias.'~gate_id'];
	unset($params[$alias.'~gate_id']);
	$pwfield = $db->GetOne('SELECT apiname FROM '.$pref.
	 'module_smsg_props WHERE gate_id=? AND apiconvert>='.SMSG::DATA_PW,array($gid));
	if($pwfield)
	  {
		$key = $alias.'~'.$pwfield.'~value';
		$params[$key] = smsg_utils::encrypt_value($params[$key]);
	  }

	$this->custom_save($params); //any gateway-specific adjustments to $params
	$delete = isset($params[$alias.'~delete']);

	//2 parts of sql command, cuz' can't parameterise inserted fieldname
	$sql1 = 'UPDATE '.$pref.'module_smsg_props SET ';
	$sql2 = '=?,apiorder=? WHERE gate_id=? AND apiname=?';
	//TODO upsert needed

	if( $delete )
	  {
		unset($params[$alias.'~delete']);
		$sql12 = 'DELETE FROM '.$pref.'module_smsg_props WHERE gate_id=? AND apiname=?';
	  }

	$srch = array(' ',"'",'"','=','\\','/','\0',"\n","\r",'\x1a');
	$repl = array('' ,'' ,'' ,'' ,''  ,'' ,''  ,''  ,''  ,'' );
	$padm = $this->_module->CheckPermission('AdministerSMSGateways');
	if($padm)
	  	$done = array();
	$nm = '';
	$o = 0; //first $nm-check increments to 1
	foreach( $params as $key=>&$val )
	  {
		//$key is like 'clickatell~user~title'
		if( strpos($key,$alias) === 0 )
		  {
			$parts = explode('~',$key); //hence [0]=$alias,[1]=apiname-field value,[2](mostly)=fieldname to update
			if( $parts[2] && $parts[2] != 'sel' )
			  {
				if( $padm && !in_array($parts[1],$done) )
				  {
				  	$done[] = $parts[1];
					//ensure checkbox-related $params are present & valid
					$key = $alias.'~'.$parts[1].'~active';
					$params[$key] = ( array_key_exists($key,$params) ) ? '1':'0';
				  }
				//foil injection-attempts
				$parts[2] = str_replace($srch,$repl,$parts[2]);
				if( $parts[2] == 'apiname' )
				  {
				  	if($parts[1])
					  {
						$parts[1] = str_replace($srch,$repl,$parts[1]);
						if( preg_match('/[^\w~@#\$%&?+-:|]/',$parts[1]) )
							continue;
					  }
					else
						$parts[1] = 'todo';
				  }
				if( $parts[1] != $nm )
				  {
				  	$nm = $parts[1];
					$o++;
				  }
				$db->Execute($sql1.$parts[2].$sql2,array($val,$o,$gid,$parts[1]));
			  }
			elseif( $delete && $parts[2] == 'sel' )
				$db->Execute($sql12,array($gid,$parts[1]));
		  }
	  }
	unset($val);
  }

  //For internal use only
  //Record or update gateway-specific details in the module's database tables
  //Returns key-value of the row added to the gates-table,for the gateway
  abstract public function upsert_tables();

  //For internal use only
  //Setup gateway-specific details for defaultadmin action
  //$padm = boolean,TRUE if current user has AdministerSMSGateways permission
  abstract public function custom_setup(&$smarty,$padm);

  //For internal use only
  //Process gateway-specific details after 'submit' in defaultadmin action
  abstract public function custom_save(&$params);

  /**
  get_name:
  Returns string which is the(un-translated) gateway identifier
  */
  abstract public function get_name();

  /**
  get_alias:
  Returns string which is the(un-translated) gateway alias,used for classname etc
  */
  abstract public function get_alias();

  /**
  get_description:
  Returns string which is a(translated) brief description of the gateway
  */
  abstract public function get_description();

  /**
  support_custom_sender:
  Returns boolean TRUE/FALSE according to whether the gateway allows use of
  a user-specified source-phone-number(which might need to be a number pre-arranged
  with the gateway supplier)
  */
  abstract public function support_custom_sender();

  /**
  require_country_prefix:
  Returns boolean TRUE/FALSE according to whether a country-code prefix for
  must be prepended to destination phone-numbers
  */
  abstract public function require_country_prefix();

  /**
  require_plus_prefix:
  Returns boolean TRUE/FALSE according to whether the country-code prefix for
  destination numbers must begin with a '+' character
  */
  abstract public function require_plus_prefix();

  /**
  multi_number_separator:
  Returns string containing character to be used for separating numbers in
  multi-destination 'to' values,or FALSE if only single-destination is allowed
  */
  abstract public function multi_number_separator();

  /**
  support_mms:
  Returns boolean TRUE/FALSE according to whether the gateway supports automatic
  partitioning of messages longer than 160 chars into a series of shorter parts
  */
  abstract public function support_mms();

  //Perform pre-message-send initialisation or checks,if any
  //Returns nothing
  abstract protected function setup();

  //Construct the actual string to be sent to the gateway
  //Returns that string,or else empty string or literal FALSE upon failure
  //May return dummy e.g. 'good' or ' ' if such string isn't needed
  abstract protected function prep_command();

  //Interpret $res(string or object,usually) returned from last message-send process
  abstract protected function parse_result($res);

  /**
  process_delivery_report:
  Interpret message-delivery report and return resultant string,suitable for
  public display or logging
  */
  abstract public function process_delivery_report();

  //For internal use only
  //Get string returned by gateway in response to message-send process
  abstract public function get_raw_status();

} // end of class

?>
