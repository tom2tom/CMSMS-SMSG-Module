<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

/**
 @mod: reference to current SMSG module object
 @tplvars: reference to array of template variables (updated here)
 @modify: boolean, whether to setup for full editing
 @dflttpl: boolean, whether to setup for editing default template
 @id: instance id of @mod
 @returnid: page id to use on subsequent forms and links
 @activetab: tab to return to
 @prefix: template full-name prefix ('enternumber_' or 'entertext_')
 @prefdefname: key of preference that contains the base-name of the current
  default template for @prefix
 */
function SetupTemplateList(&$mod,&$tplvars,$modify,$dflttpl,
	$id,$returnid,$activetab,
	$prefix,$prefdefname)
{
	if($modify)
	{
		$theme = ($mod->before20) ? cmsms()->get_variable('admintheme'):
			cms_utils::get_theme_object();
		$trueicon = $theme->DisplayImage('icons/system/true.gif',$mod->Lang('default_tip'),'','','systemicon');
		$falseicon = $theme->DisplayImage('icons/system/false.gif',$mod->Lang('defaultset_tip'),'','','systemicon');
		$addicon = $theme->DisplayImage('icons/system/newobject.gif',$mod->Lang('add_template'),'','','systemicon');
		$editicon = $theme->DisplayImage('icons/system/edit.gif',$mod->Lang('edit_tip'),'','','systemicon');
		$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$mod->Lang('deleteone_tip'),'','','systemicon');
		$prompt = $mod->Lang('sure_ask');
		$args = array('prefix'=>$prefix,'activetab'=>$activetab);
	}
	else
		$yes = $mod->Lang('yes');

	$defaultname = $mod->GetPreference($prefdefname);
	$rowarray = array();

	if($mod->before20)
	{
		$mytemplates = $mod->ListTemplates(SMSG::MODNAME); //TODO filter by type/prefix
		//exclude unwanted types, and wanted type's 'defaultcontent' template (anonymous callback >> PHP 5.3+)
		array_walk($mytemplates,function(&$n,$k,$p)
		{
			$l=strlen($p);
			$n=(strncmp($n,$p,$l) === 0)?substr($n,$l):FALSE;
			if($n=='defaultcontent')
			 $n=FALSE;
		},$prefix);
		$mytemplates = array_filter($mytemplates);
	}
	else
	{
		$name = rtrim($prefix,'_');
		$ttype = CmsLayoutTemplateType::load('SMSG::'.$name);
		$mytemplates = $ttype->get_template_list();
		//TODO cleanup names, for presentation
	}
	sort($mytemplates,SORT_LOCALE_STRING);

	foreach($mytemplates as $one)
	{
		$default = ($one == $defaultname);
		$oneset = new StdClass();
		if($modify)
		{
			$args['template'] = $one;
			$args['mode'] = 'edit';
			$oneset->name = $mod->CreateLink($id,'settemplate',$returnid,$one,$args);
			$oneset->editlink = $mod->CreateLink($id,'settemplate',$returnid,$editicon,$args);

			$args['mode'] = 'default';
			$oneset->default = ($default) ?
				$trueicon:
				$mod->CreateLink($id,'settemplate',$returnid,$falseicon,$args);

			$args['mode'] = 'delete';
			$oneset->deletelink = ($default) ?
				'':
				$mod->CreateLink($id,'settemplate',$returnid,$deleteicon,$args,$prompt);
		}
		else
		{
			$oneset->name = $one;
			$oneset->default = ($default) ? $yes:'';
			$oneset->editlink = '';
			$oneset->deletelink = '';
		}
		$rowarray[] = $oneset;
	}

	if($modify && $dflttpl)
	{
		$oneset = new StdClass();
		$args['template'] = 'defaultcontent';
		$args['mode'] = 'edit';
		$oneset->name = $mod->CreateLink($id,'settemplate',$returnid,
			'<em>'.$mod->Lang('default_template_title').'</em>',$args);
		$oneset->editlink = $mod->CreateLink($id,'settemplate',$returnid,$editicon,$args);

		$oneset->default = '';

		$reverticon = '<img src="'.$mod->GetModuleURLPath().'/images/revert.gif" alt="'.
		 $mod->Lang('reset').'" title="'.$mod->Lang('reset_tip').
		 '" class="systemicon" onclick="return confirm(\''.$prompt.'\');" />';
		$args['mode'] = 'revert';
		$oneset->deletelink = $mod->CreateLink($id,'settemplate',$returnid,$reverticon,$args);
		$rowarray[] = $oneset;
	}

	$tplvars += array(
		$prefix.'items' => $rowarray,
		'parent_module_name' => $mod->GetFriendlyName(),
		'titlename' => $mod->Lang('name'),
		'titledefault' => $mod->Lang('default')
	);
	if($modify)
	{
		$args['mode'] = 'add';
		$add = $mod->CreateLink($id,'settemplate',$returnid,$addicon,$args).' '.
			$mod->CreateLink($id,'settemplate',$returnid,$mod->Lang('add_template'),$args);
	}
	else
		$add = '';
	$tplvars['add_'.$prefix.'template'] = $add;
}

smsg_utils::refresh_gateways($this);
$objs = smsg_utils::get_gateways_full($this);
if(!$objs)
{
	echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
	return;
}

$padm = $this->CheckPermission('AdministerSMSGateways');
$pmod = $padm || $this->CheckPermission('ModifySMSGateways');
$ptpl = $padm || $this->CheckPermission('ModifySMSGateTemplates');
$puse = $this->CheckPermission('UseSMSGateways');

$tplvars = array(
	'padm' => $padm,
	'pmod' => $pmod,
	'ptpl' => $ptpl,
	'puse' => $puse
);

if(!empty($params['activetab']))
	$showtab = $params['activetab'];
else
	$showtab = 'gates'; //default

$headers = $this->StartTabHeaders();
if($pmod || $puse)
	$headers .=
 $this->SetTabHeader('gates',$this->Lang('gateways'),($showtab=='gates')).
 $this->SetTabHeader('test',$this->Lang('test'),($showtab=='test')).
 $this->SetTabHeader('mobiles',$this->Lang('phone_numbers'),($showtab=='mobiles'));
if($ptpl || $puse)
	$headers .=
 $this->SetTabHeader('enternumber',$this->Lang('enter_number_templates'),($showtab=='enternumber')).
 $this->SetTabHeader('entertext',$this->Lang('enter_text_templates'),($showtab=='entertext'));
if($padm)
	$headers .=
 $this->SetTabHeader('security',$this->Lang('security_tab_lbl'),($showtab=='security'));
$headers .=
 $this->EndTabHeaders().
 $this->StartTabContent();

$tplvars += array(
	'tabsheader' => $headers,
	'tabsfooter' => $this->EndTabContent(), //for CMSMS 2+, must be before EndTab() !!
	'endtab' => $this->EndTab(),
	'formend' => $this->CreateFormEnd(),
	//various titles
	'default_gateway' => $this->Lang('default_gateway'),
	'id' => $this->Lang('id'),
	'info_smstest' => $this->Lang('info_smstest'),
	'number' => $this->Lang('number'),
	'phone_number' => $this->Lang('phone_number'),
	'reporting_url' => $this->Lang('reporting_url'),
	'title_dailylimit' => $this->Lang('prompt_daily_limit'),
	'title_hourlylimit' => $this->Lang('prompt_hourly_limit'),
	'title_logdelivers' => $this->Lang('prompt_log_delivers'),
	'title_logretain' => $this->Lang('prompt_log_retain_days'),
	'title_logsends' => $this->Lang('prompt_log_sends'),
	'title_password' => $this->Lang('prompt_master_password'),
	'submit' => $this->Lang('submit'),
	'cancel' => $this->Lang('cancel')
);

$jsincs = array();
$jsfuncs = array();
$jsloads = array();
$baseurl = $this->GetModuleURLPath();

if($pmod || $puse)
{
	$tplvars += array(
		'tabstart_gates' => $this->StartTab('gates',$params),
		'formstart_gates' => $this->CreateFormStart($id,'savegates'),
		'reporturl' => smsg_utils::get_reporturl($this)
	);

	if($pmod)
	{
		$current = $db->GetOne('SELECT alias FROM '.cms_db_prefix().
			'module_smsg_gates WHERE enabled=1 AND active=1');
		if($current == FALSE)
			$current = '-1';
		$tplvars['gatecurrent'] = $current;
	
		$names = array(-1 => $this->Lang('none'));
		foreach($objs as $key=>&$rec)
		{
			$names[$key] = $rec['obj']->get_name();
			$rec = $rec['obj']->get_setup_form();
		}
		unset($rec);
		$tplvars['gatesnames'] = $names;
	}
	else
	{
		foreach($objs as $key=>&$rec)
			$rec = $rec['obj']->get_setup_form();
		unset($rec);
	}
	$tplvars['gatesdata'] = $objs;

	$theme = ($this->before20) ? cmsms()->get_variable('admintheme'):
		cms_utils::get_theme_object();

	$tplvars['tabstart_test'] = $this->StartTab('test',$params);
	$tplvars['formstart_test'] = $this->CreateFormStart($id,'smstest');

	$tplvars['tabstart_mobiles'] = $this->StartTab('mobiles',$params);
	$query = 'SELECT * FROM '.cms_db_prefix().'module_smsg_nums ORDER BY id';
	$data = $db->GetAll($query);
	if($data)
	{
		$editicon = $theme->DisplayImage('icons/system/edit.gif',$mod->Lang('edit_tip'),'','','systemicon');
		$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$mod->Lang('deleteone_tip'),'','','systemicon');
		$prompt = $this->Lang('ask_delete_mobile');
		foreach($data as &$row)
		{
			$row = (object)$row;
			if($pmod)
			{
				$args = array('mid'=>$row->id);
				$rec->editlink = $this->CreateLink($id,'edit_mobile','',$editicon,$args);
				$rec->deletelink = $this->CreateLink($id,'del_mobile','',$deleteicon,$args,$prompt);
			}
		}
		unset($row);
		$tplvars['numbers'] = $data;
	}
	else
		$tplvars['nonumbers'] = $this->Lang('nonumbers');
	if($pmod)
	{
		$text = $this->Lang('add_mobile');
		$addicon = $theme->DisplayImage('icons/system/newobject.gif',$text,'','','systemicon');
		$tplvars['add_mobile'] = $this->CreateLink($id,'edit_mobile','',$addicon).' '.
			$this->CreateLink($id,'edit_mobile','',$text);
	}
}

if($ptpl || $puse)
{
	$tid = 'enternumber'; //tab identifier
	$tplvars['tabstart_enternumber'] = $this->StartTab($tid,$params);
	SetupTemplateList($this,$tplvars,$ptpl,$padm,
		$id,$returnid,$tid, //tab to come back to
		'enternumber_', //'prefix' of templates' full-name
		SMSG::PREF_ENTERNUMBER_TPLDFLT); //preference holding name of default template

	$tid = 'entertext';
	$tplvars['tabstart_entertext'] = $this->StartTab($tid,$params);
	SetupTemplateList($this,$tplvars,$ptpl,$padm,
		$id,$returnid,$tid,'entertext_',SMSG::PREF_ENTERTEXT_TPLDFLT);
}

if($padm)
{
	$tplvars += array(
		'tabstart_security' => $this->StartTab('security',$params),
		'formstart_security' => $this->CreateFormStart($id,'savesecurity'),
		'hourlimit' => $this->GetPreference('hourlimit'),
		'daylimit' => $this->GetPreference('daylimit'),
		'logsends' => $this->GetPreference('logsends'),
		'logdays' => $this->GetPreference('logdays'),
		'logdeliveries' => $this->GetPreference('logdeliveries')
	);
	$pw = $this->GetPreference('masterpass');
	if($pw)
		$pw = smsg_utils::unfusc($pw);
	$tplvars['masterpass'] = $pw; 
	$jsincs[] = '<script type="text/javascript" src="'.$baseurl.'/include/jquery-inputCloak.min.js"></script>';
	$jsloads[] =<<<EOS
 $('#{$id}passwd').inputCloak({
  type:'see4',
  symbol:'\u2022'
 });

EOS;
}

//show only the frameset for selected gateway
$jsloads[] = <<<EOS
 $('.sms_gateway_panel').hide();
 var \$sel = $('#sms_gateway'),
    val = \$sel.val();
 $('#'+val).show();

EOS;
if($padm)
{
	$prompt = $this->Lang('sure_ask');
	$jsloads[] = <<<EOS
 \$sel.change(function() {
   $('.sms_gateway_panel').hide();
   var val = $(this).val();
   $('#'+val).show();
 });
 $('input[type="submit"][name$="~delete"]').click(function(ev) {
  var cb = $(this).closest('fieldset').find('input[name$="~sel"]:checked');
  if(cb.length > 0) {
   return confirm('{$prompt}');
  } else {
   return false;
  }
 });

EOS;
	//support property reordering by table-DnD
	$jsincs[] = <<<EOS
<script type="text/javascript" src="'{$baseurl}/include/jquery.tablednd.min.js"></script>

EOS;
	$jsloads[] = <<<EOS
 $('.gatedata').tableDnD({
  dragClass: 'row1hover',
  onDrop: function(table, droprows) {
   var odd = true;
   var oddclass = 'row1';
   var evenclass = 'row2';
   var droprow = $(droprows)[0];
   $(table).find('tbody tr').each(function() {
    var name = odd ? oddclass : evenclass;
    if (this === droprow) {
     name = name+'hover';
    }
    $(this).removeClass().addClass(name);
    odd = !odd;
   });
  }
 }).find('tbody tr').removeAttr('onmouseover').removeAttr('onmouseout').mouseover(function() {
  var now = $(this).attr('class');
  $(this).attr('class', now+'hover');
 }).mouseout(function() {
  var now = $(this).attr('class');
  var to = now.indexOf('hover');
  $(this).attr('class', now.substring(0,to));
 });

EOS;
}

$jsfuncs[] = <<<EOS
$(document).ready(function() {

EOS;
$jsfuncs = array_merge($jsfuncs,$jsloads);
$jsfuncs[] = <<<EOS
});

EOS;

$tplvars['jsincs'] = $jsincs;
$tplvars['jsfuncs'] = $jsfuncs;

echo smsg_utils::ProcessTemplate($this,'adminpanel.tpl',$tplvars);

?>
