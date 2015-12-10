<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

class smsg_clearlog_task implements CmsRegularTask
{
	public function get_name() 
	{
		return get_class($this);
	}

	public function get_description()
	{
		$mod = cms_utils::get_module(SMSG::MODNAME);
		return $mod->Lang('taskdescription_clearlog');
	}

	public function test($time = '')
	{
		$mod = cms_utils::get_module(SMSG::MODNAME);
		if(!($mod->GetPreference('logsends')
		  || $mod->GetPreference('logdeliveries')))
			return FALSE;
		$days = (int)$mod->GetPreference('logdays');
		if($days <= 0)
			return FALSE;
		if(!$time)
			$time = time();
		$last_cleared = $mod->GetPreference('lastcleared');
		return ($time >= $last_cleared + $days*86400);
	}

	public function execute($time = '')
	{
		if(!$time)
			$time = time();
		smsg_utils::clean_log(NULL,$time);
		return TRUE;
	}

	public function on_success($time = '')
	{
		if(!$time)
			$time = time();
		$mod = cms_utils::get_module(SMSG::MODNAME);
		$mod->SetPreference('lastcleared',$time);
	}

	public function on_failure($time = '')
	{
	}
}

?>
