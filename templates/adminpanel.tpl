{if !empty($message)}<p>{$message}</p>{/if}

{$tabsheader}
{if ($pmod || $puse)}
{$tabstart_gates}
{if $pmod}
{$formstart_gates}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('reporting_url')}:</p>
 <p class="pageinput">{$reporturl}</p>
 <br />
 <p class="pagetext">{$mod->Lang('default_gateway')}:</p>
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
  <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />  
 </p>
</div>
{$formend}
{else}
<div class="pageoverflow">
<table class="pagetable" style="border:0;">
 <thead><tr>
  <th>{$titlename}</th>
  <th>{$titledefault}</th>
 </tr></thead>
<tbody>
{foreach from=$gatesdata item=one}
{cycle name=gates values="row1,row2" assign='rowclass'}
 <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
{$one}
 </tr>
{/foreach}
</tbody></table>
</div>
{/if}{*$pmod*}
{$endtab}

{$tabstart_test}
<p>{$mod->Lang('info_smstest')}</p>
{$formstart_test}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('phone_number')}:</p>
 <p class="pageinput"><input type="text" name="{$actionid}mobile" size="20" maxlength="20" /></p>
</div>
<br />
<div class="pageoverflow">
 <p class="pageinput"><input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" /></p>
</div>
{$formend}
{$endtab}

{$tabstart_mobiles}
{if !empty($numbers)}<table class="pagetable" style="border:0;">
 <thead><tr>
  <th>{$mod->Lang('id')}</th>
  <th>{$titlename}</th>
{if $pmod} <th>{$mod->Lang('number')}</th>
  <th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>{/if}
 </tr></thead>
 <tbody>
{foreach from=$numbers item=one}
{cycle name=phones values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one->id}</td>
   <td>{$one->name}</td>
   <td>{$one->mobile}</td>
{if $pmod} <td>{$one->editlink}</td>
   <td>{$one->deletelink}</td>{/if}
  </tr>
{/foreach}
 </tbody>
</table>{else}
<p>{$nonumbers}</p>
{/if}{*$numbers*}
{if $pmod}<div class="pageoptions">
{$add_mobile}
</div>{/if}
{$endtab}
{/if}{*$pmod||$puse*}

{if ($ptpl|| $puse)}
{$tabstart_enternumber}
<div class="pageoverflow">
<table class="pagetable">
 <thead><tr>
  <th>{$titlename}</th>
  <th>{$titledefault}</th>
{if $ptpl}<th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>{/if}
 </tr></thead>
 <tbody>
{foreach from=$enternumber_items item=one}
{cycle name=numtpl values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one->name}</td>
   <td>{$one->default}</td>
{if $ptpl} <td>{$one->editlink}</td>
   <td>{$one->deletelink}</td>{/if}
  </tr>
{/foreach}
 </tbody>
</table>
{if $ptpl}<br />
<p class="pageoptions">{$add_enternumber_template}</p>{/if}{*$ptpl*}
</div>
{$endtab}
{$tabstart_entertext}
<div class="pageoverflow">
<table class="pagetable">
 <thead><tr>
  <th>{$titlename}</th>
  <th>{$titledefault}</th>
{if $ptpl} <th class="pageicon">&nbsp;</th>
  <th class="pageicon">&nbsp;</th>{/if}
 </tr></thead>
 <tbody>
{foreach from=$entertext_items item=one}
{cycle name=texttpl values="row1,row2" assign='rowclass'}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
   <td>{$one->name}</td>
   <td>{$one->default}</td>
{if $ptpl} <td>{$one->editlink}</td>
   <td>{$one->deletelink}</td>{/if}
  </tr>
{/foreach}
 </tbody>
</table>
{if $ptpl}<br />
<p class="pageoptions">{$add_entertext_template}</p>{/if}{*$ptpl*}
</div>
{$endtab}
{/if}{*$ptpl||$puse*}

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
  <textarea id="{$actionid}passwd" name="{$actionid}masterpass" class="cloaked" rows="2" cols="40">{$masterpass}</textarea>
 </p>
 <br />
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
  <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
 </p>
</div>
{$formend}
{$endtab}
{/if}{*$padm*}
{$tabsfooter}

{if !empty($jsincs)}{foreach from=$jsincs item=file}{$file}
{/foreach}{/if}
{if !empty($jsfuncs)}
<script type="text/javascript">
//<![CDATA[
{foreach from=$jsfuncs item=func}{$func}{/foreach}
//]]>
</script>
{/if}
