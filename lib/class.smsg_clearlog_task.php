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
		$module = cms_utils::get_module(SMSG::MODNAME);
		return $module->Lang('taskdescription_clearlog');
	}

	public function test($time = '')
	{
		$module = cms_utils::get_module(SMSG::MODNAME);
		if(!($module->GetPreference('logsends')
		  || $module->GetPreference('logdeliveries')))
			return FALSE;
		$days = (int)$module->GetPreference('logdays');
		if($days <= 0)
			return FALSE;
		if(!$time)
			$time = time();
		$last_cleared = $module->GetPreference('lastcleared');
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
		$module = cms_utils::get_module(SMSG::MODNAME);
		$module->SetPreference('lastcleared',$time);
	}

	public function on_failure($time = '')
	{
	}
}

?>
