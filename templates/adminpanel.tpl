{if !empty($message)}<p>{$message}</p>{/if}

{$starttabcontent}
{if $pmod}
{$tabstart_mobiles}
{if !empty($mobiles)}<table class="pagetable" style="border:0;">
 <thead><tr>
  <th>{$mod->Lang('id')}</th>
  <th>{$mod->Lang('name')}</th>
  <th>{$mod->Lang('number')}</th>
  <th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>
 </tr></thead>
 <tbody>
{foreach from=$mobiles item=one}
{cycle values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one.id}</td>
   <td>{$one.name}</td>
   <td>{$one.mobile}</td>
   <td>{$one.edit_link}</td>
   <td>{$one.del_link}</td>
  </tr>
{/foreach}
 </tbody>
</table>{/if}{*$mobiles*}
<div class="pageoptions">
{$add_mobile}
</div>
{$endtab}

{$tabstart_settings}
{$formstart_settings}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('reporting_url')}:</p>
 <p class="pageinput">{$reporturl}</p>
 <br />
 <p class="pagetext">{$mod->Lang('selected_gateway')}:</p>
 <p class="pageinput">
  <select id="sms_gateway" name="{$actionid}sms_gateway"> 
   {html_options options=$gatesnames selected=$gatecurrent}
  </select>
 </p>
</div>

{foreach from=$gatesdata key=alias item=one}
<div id="{$alias}" class="pageoverflow sms_gateway_panel" style="margin:0.5em 0;">
{$one}
</div>
{/foreach}
<br />
<div class="pageoverflow">
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
 </p>
</div>
{$formend}
{$endtab}

{$tabstart_test}
<p>{$mod->Lang('info_smstest')}</p>
{$formstart_test}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('mobile_number')}:</p>
 <p class="pageinput"><input type="text" name="{$actionid}mobile" size="20" maxlength="20" /></p>
</div>
<br />
<div class="pageoverflow">
 <p class="pageinput"><input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" /></p>
</div>
{$formend}
{$endtab}
{/if}{*$pmod*}

{if $ptpl}
{$tabstart_enternumber}
<div class="pageoverflow">
<table class="pagetable">
 <thead><tr>
  <th>{$nameprompt}</th>
  <th>{$defaultprompt}</th>
  <th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>
 </tr></thead>
 <tbody>
{foreach from=$enternumber_items item=one}
{cycle values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one->name}</td>
   <td>{$one->default}</td>
   <td>{$one->editlink}</td>
   <td>{$one->deletelink}</td>
  </tr>
{/foreach}
 </tbody>
</table>
{if $padm}<br />
<p class="pageoptions">{$add_enternumber_template}</p>{/if}{*$padm*}
</div>
{$endtab}
{$tabstart_entertext}
<div class="pageoverflow">
<table class="pagetable">
 <thead><tr>
  <th>{$nameprompt}</th>
  <th>{$defaultprompt}</th>
  <th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>
 </tr></thead>
 <tbody>
{foreach from=$entertext_items item=one}
{cycle values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one->name}</td>
   <td>{$one->default}</td>
   <td>{$one->editlink}</td>
   <td>{$one->deletelink}</td>
  </tr>
{/foreach}
 </tbody>
</table>
{if $padm}<br />
<p class="pageoptions">{$add_entertext_template}</p>{/if}{*$padm*}
</div>
{$endtab}
{/if}{*$ptpl*}

{if $padm}
{$tabstart_security}
{$formstart_security}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('prompt_hourly_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}hourlimit" value="{$hourlimit}" size="3" maxlength="3" />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_daily_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}daylimit" value="{$daylimit}" size="3" maxlength="4" />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_log_sends')}:</p>
 <p class="pageinput">
  <input type="checkbox" name="{$actionid}logsends"{if $logsends} checked="checked"{/if} />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_log_retain_days')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}logdays" value="{$logdays}" size="2" maxlength="3" />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_log_delivers')}:</p>
 <p class="pageinput">
  <input type="checkbox" name="{$actionid}logdeliveries"{if $logdeliveries} checked="checked"{/if} />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_master_password')}:</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}masterpass" value="{$masterpass}" size="20" maxlength="64" />
 </p>
 <br />
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
 </p>
</div>
{$formend}
{$endtab}
{/if}{*$padm*}
{$endtabcontent}
