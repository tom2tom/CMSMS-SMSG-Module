<fieldset>
<legend>{$gatetitle}</legend>
<div class="pageoverflow" style="margin-top:0;">
<table class="pagetable gatedata" style="margin-top:0;">
<thead><tr>
<th>{$titletitle}</th>
<th>{$titlevalue}</th>
<th>{$titleapiname}</th>
<th>{$titlehelp}</th>
<th>{$titleselect}</th>
</tr></thead>
<tbody>
{foreach from=$data item=one name=block}
{cycle values="row1,row2" assign=rowclass}
<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
<td>
 <input type="text" name="{$actionid}{$space}~{$one->apiname}~title" size="15" value="{$one->title}" />
</td>
<td>
 <input type="{if !empty($one->pass)}password{else}text{/if}" name="{$actionid}{$space}~{$one->apiname}~value" size="{if !empty($one->size)}{$one->size}{else}15{/if}" value="{$one->value}" />
</td>
<td>
 <input type="text" name="{$actionid}{$space}~{$one->apiname}~apiname" size="15" value="{$one->apiname}" />
</td>
<td>{if !empty($one->help)}{$one->help}>{/if}</td>
<td>
 <input type="checkbox" name="{$actionid}{$space}~{$one->apiname}~check" />
</td>
</tr>
{/foreach}
</tbody>
</table>
<br />
{$help}
<br /><br />
<div class="pageoptions">
{$additem}
{if $dcount}<div style="margin:0;float:right;text-align:right">{$btndelete}</div>
<div style="float:clear"></div>{/if}
</div>
<input type="hidden" name="{$actionid}{$space}~gate_id" value="{$gateid}" />
{if !empty($hidden)}{$hidden}{/if}
</fieldset>
