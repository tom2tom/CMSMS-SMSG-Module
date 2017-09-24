<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

namespace SMSG;

class ClearlogTask implements \CmsRegularTask
{
	public function get_name()
	{
		return get_class();
	}

	protected function &get_module()
	{
		return \ModuleOperations::get_instance()->get_module_instance('SMSG', '', TRUE);
	}

	public function get_description()
	{
		return $this->get_module()->Lang('taskdescription_clearlog');
	}

	public function test($time = '')
	{
		$mod = $this->get_module();
		if (!($mod->GetPreference('logsends')
		   || $mod->GetPreference('logdeliveries'))) {
			return FALSE;
		}
		$days = (int)$mod->GetPreference('logdays');
		if ($days <= 0) {
			return FALSE;
		}
		if (!$time) {
			$time = time();
		}
		$last_cleared = $mod->GetPreference('lastcleared');
		return ($time >= $last_cleared + $days*86400);
	}

	public function execute($time = '')
	{
		if (!$time) {
			$time = time();
		}
		$mod = $this->get_module();
		\SMSG\Utils::clean_log($mod, $time);
		return TRUE;
	}

	public function on_success($time = '')
	{
		if (!$time) {
			$time = time();
		}
		$this->get_module()->SetPreference('lastcleared', $time);
	}

	public function on_failure($time = '')
	{
	}
}
