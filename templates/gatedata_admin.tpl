<input type="hidden" name="{$actionid}{$space}~gate_id" value="{$gateid}" />
{if !empty($hidden)}{$hidden}{/if}
<fieldset>
<legend>{$gatetitle}</legend>
<div class="pageoverflow" style="margin-top:0;display:inline-block;width:auto;">
<table class="pagetable gatedata" style="margin-top:0;">
<thead><tr>
<th>{$title_title}</th>
<th>{$title_value}</th>
<th>{$title_encrypt}</th>
<th>{$title_apiname}</th>
<th>{$title_enabled}</th>
<th>{$title_help}</th>
<th>{$title_select}</th>
</tr></thead>
<tbody>
{foreach from=$data item=one name=block}
{cycle values="row1,row2" assign=rowclass}
<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
<td><input type="text" name="{$actionid}{$one->space}title" size="15" value="{$one->title}" /></td>
<td><input type="text" name="{$actionid}{$one->space}value" size="{if !empty($one->size)}{$one->size}{else}15{/if}" value="{$one->value}" /></td>
<td><input type="checkbox" name="{$actionid}{$one->space}encrypt"{if $one->encrypt} checked="checked"{/if} /></td>
<td><input type="text" name="{$actionid}{$one->space}apiname" size="15" value="{$one->apiname}" /></td>
<td><input type="checkbox" name="{$actionid}{$one->space}enabled"{if $one->enabled} checked="checked"{/if} /></td>
<td>{if !empty($one->help)}{$one->help}>{/if}</td>
<td><input type="checkbox" name="{$actionid}{$one->space}sel" /></td>
</tr>
{/foreach}
</tbody>
</table>
<br />
{$help}
<br /><br />
<div class="pageoptions">
{$additem}
{if $dcount}<div style="float:right;">{$btndelete}</div>
<div style="float:clear"></div>{/if}
</div>
</fieldset>
