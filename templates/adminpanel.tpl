{if !empty($message)}<p>{$message}</p>{/if}

{$tabsheader}
{if ($pmod || $puse)}
{$tabstart_gates}
{if $pmod}
{$formstart_gates}
<div class="pageinput pageoverflow">
 <p class="pagetext">{$reporting_url}:</p>
 <p>{$reporturl}</p>
 <p class="pagetext">{$default_gateway}:</p>
 <select id="sms_gateway" name="{$actionid}sms_gateway">
  {html_options options=$gatesnames selected=$gatecurrent}
 </select>
{foreach from=$gatesdata key=alias item=one}
 <div id="{$alias}" class="sms_gateway_panel" style="margin:0.5em 0;">
{$one}
 </div>
{/foreach}
 <p style="margin-top:1em">
 <input type="submit" name="{$actionid}submit" value="{$submit}" />
 <input type="submit" name="{$actionid}cancel" value="{$cancel}" />
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
<div class="pageinput pageoverflow">
<p>{$info_smstest}</p>
{$formstart_test}
 <p class="pagetext">{$phone_number}:</p>
 <input type="text" name="{$actionid}mobile" size="20" maxlength="20" />
 <br /><br />
 <input type="submit" name="{$actionid}submit" value="{$submit}" />
{$formend}
</div>
{$endtab}

{$tabstart_mobiles}
<div class="pageinput pageoverflow">
{if !empty($numbers)}<table class="pagetable" style="border:0;">
 <thead><tr>
  <th>{$id}</th>
  <th>{$titlename}</th>
{if $pmod} <th>{$number}</th>
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
</div>
{$endtab}
{/if}{*$pmod||$puse*}

{if ($ptpl|| $puse)}
{$tabstart_enternumber}
<div class="pageinput pageoverflow">
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
<div class="pageinput pageoverflow">
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
<div class="pageinput pageoverflow">
 <p class="pagetext">{$title_hourlylimit}:</p>
 <input type="text" name="{$actionid}hourlimit" value="{$hourlimit}" size="3" maxlength="3" />
 <p class="pagetext">{$title_dailylimit}:</p>
 <input type="text" name="{$actionid}daylimit" value="{$daylimit}" size="3" maxlength="4" />
 <p class="pagetext">{$title_logsends}:</p>
 <input type="checkbox" name="{$actionid}logsends"{if $logsends} checked="checked"{/if} />
 <p class="pagetext">{$title_logretain}:</p>
 <input type="text" name="{$actionid}logdays" value="{$logdays}" size="2" maxlength="3" />
 <p class="pagetext">{$title_logdelivers}:</p>
 <input type="checkbox" name="{$actionid}logdeliveries"{if $logdeliveries} checked="checked"{/if} />
 <p class="pagetext">{$title_password}:</p>
 <textarea id="{$actionid}passwd" name="{$actionid}masterpass" class="cloaked" rows="2" cols="40">{$masterpass}</textarea>
 <br /><br />
 <input type="submit" name="{$actionid}submit" value="{$submit}" />
 <input type="submit" name="{$actionid}cancel" value="{$cancel}" />
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
