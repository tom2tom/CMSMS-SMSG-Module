<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: CGSMS (C) 2010-2015 Robert Campbell (calguy1000@cmsmadesimple.org)
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

class skeleton_sms_gateway extends cgsms_sender_base
{
	private $_rawstatus;

	public function get_name()
	{
		//DEPRECATED see database table
		return '';
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

	public function get_setup_form()
	{
		$smarty = cmsms()->GetSmarty();
		$mod = $this->get_module();
		//TODO setup smarty vars for this gateway's template
		return $mod->ProcessTemplate('TODO_setup.tpl');
	}

	public function handle_setup_form($params)
	{
		$mod = $this->get_module();
		//TODO store data from $params from gateway's template
	}

	protected function setup()
	{
		//TODO
	}

	protected function prep_command()
	{
		//TODO
		return $str;
	}

	protected function parse_result($str)
	{
		$this->_rawstatus = $str;
		//TODO
	}

	public function _process_delivery_report()
	{
		//TODO
		return '';
	}

	public function get_raw_status()
	{
		return $this->_rawstatus;
	}
}

?>
