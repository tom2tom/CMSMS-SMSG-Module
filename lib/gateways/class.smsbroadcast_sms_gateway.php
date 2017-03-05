<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------
namespace SMSG\gateways;

class smsbroadcast_sms_gateway extends \SMSG\base_sms_gateway
{
	const SMSBC_API_URL = 'https://www.smsbroadcast.com.au/Advanced%20HTTP%20API.pdf';
	private $rawstatus;

	public function get_name()
	{
		return 'Smsbroadcast';
	}

	public function get_alias()
	{
		return 'smsbroadcast';
	}

	public function get_description()
	{
		return $this->mod->Lang('description_smsbroadcast');
	}

	public function support_custom_sender()
	{
		return TRUE;
	}

	public function support_mms()
	{
		return TRUE; //TODO send parameter maxsplit (up to 5)
	}

	public function require_country_prefix()
	{
		return TRUE; //actually, preferred but not mandatory
	}

	public function require_plus_prefix()
	{
		return FALSE;
	}

	public function multi_number_separator()
	{
		return ',';
	}

	public function upsert_tables()
	{
		$gid = \SMSG\Utils::setgate($this);
		if($gid) {
			parent::set_gateid($gid);
			$mod = $this->mod;
		    //setprops() argument $props = array of arrays, each with [0]=title [1]=apiname [2]=value [3]=encrypt
			\SMSG\Utils::setprops($gid,[
			 [$mod->Lang('username'),'username',NULL,0],
			 [$mod->Lang('password'),'password',NULL,1],
			 [$mod->Lang('from'),'from',NULL,0],
			 [$mod->Lang('reference'),'ref',NULL,0]
			]);
		}
		return $gid;
	}

	public function custom_setup(&$tplvars,$padm)
	{
		foreach($tplvars['data'] as &$ob) {
			if($ob->signature == 'password') {
				$ob->size = 20;
				break;
			}
		}
		unset($ob);
		if($padm) {
			$tplvars['help'] .= '<br />'.
				$this->mod->Lang('help_urlcheck',self::SMSBC_API_URL,self::get_name().' API');
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
		return 'good'; //anything which passes upstream test
	}

	protected function command($dummy)
	{
		$gid = parent::get_gateid(self::get_alias());
		$parms = \SMSG\Utils::getprops($this->mod,$gid);
		if($parms['username']['value'] == FALSE ||
		   $parms['password']['value'] == FALSE) {
			$this->status = parent::STAT_ERROR_AUTH;
			return FALSE;
		}

		$to = $this->num;
		$text = strip_tags($this->msg);
		if(!self::support_mms()) {
			$text = substr($text,0,160);
		}
		if(!$to || !\SMSG\Utils::text_is_valid($text,0)) {
			$this->status = parent::STAT_ERROR_INVALID_DATA;
			return FALSE;
		}

		$source = $this->fromnum; //can be text e.g. 'MyCompany';
		$ref = ''; //'abc123';

		$ch = curl_init('https://api.smsbroadcast.com.au/api-adv.php');
		if(!$ch) {
			$this->status = parent::STAT_ERROR_OTHER;
			return FALSE;
		}

		foreach($parms as &$val) {
			$val = rawurlencode($val['value']);
		}
		unset($val);

		$parms['to'] = rawurlencode($to);
		if($source) {
			$parms['from'] = rawurlencode($source);
		}
		$parms['message'] = rawurlencode($text);
		if($ref) {
			$parms['ref'] = rawurlencode($ref);
		}

		$str = \SMSG\Utils::implode_with_key($parms);
		$str = str_replace('amp;','',$str);

		curl_setopt($ch,CURLOPT_POST,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$str);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	protected function parse_result($str)
	{
		$lines = explode('\n',$str);
		foreach($lines as $oneline) {
			$message_data = explode(':',$oneline);
			switch ($message_data[0]) {
			 case 'OK':
				$this->rawstatus = '';
				$this->status = parent::STAT_OK;
				break;
			 case 'BAD':
				$this->rawstatus = $message_data[2];
				$this->status = parent::STAT_NOTSENT;
				break;
			 case 'ERROR':
				$this->rawstatus = $message_data[1];
				$this->status = parent::STAT_ERROR_OTHER;
				break;
			}
		}
	}

	/*
	Must parse $_REQUEST directly
	Gateway returns: to, ref, smsref, status
	 to: mobile number the message was sent to, in international format (614xxxxxxxx)
	 ref: sender's reference number, if provided when message was sent
	 smsref: SMS Broadcast reference number as returned by the API when the message was sent
	 status: current status of the message. One of
	  Delivered – The message was successfully delivered
	  Expired – The message could not be delivered within the required time
	  Failed – There was a problem with the message (e.g. incorrect mobile number, or mobile service disconnected)

	Sample request:
	http://www.yoururl.com?to=61400111222&ref=112233&smsref=1122334455&status=Delivered
	*/
	public function process_delivery_report()
	{
		switch ($_REQUEST['status']) {
		 case 'Delivered':
			$status = parent::DELIVERY_OK;
			break;
		 case 'Failed':
		 case 'Expired':
			$status = parent::DELIVERY_INVALID;
			break;
		 default:
			$status = parent::DELIVERY_UNKNOWN;
			break;
		}
		$smsid = $_REQUEST['smsref'];
		$smsto = $_REQUEST['to'];
		return \SMSG\Utils::get_delivery_msg($this->mod,$status,$smsid,$smsto);
	}

	public function get_raw_status()
	{
		return $this->rawstatus;
	}

}
