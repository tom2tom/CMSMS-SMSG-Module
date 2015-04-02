<fieldset>
<legend>{$gatetitle}</legend>
<div class="pageoverflow" style="margin-top:0;">
{foreach from=$data item=param name=block}
 <p class="pagetext"{if $smarty.foreach.block.first} style="margin-top:0;"{/if}>{$param.title}</p>
{if $param.name}<div class="pageinput">
  <input type="{if $param.pass}password{else}text{/if}" name="{$actionid}{$param.name}" size="{if !empty($param.size)}{$param.size}{else}20{/if}" value="{$param.value}" />
 </div>{/if}
{if !empty($param.help)}<p class="pageinput">{$param.help}</p>{/if}
{/foreach}
</div>
{if !empty($hidden)}{$hidden}{/if}
</fieldset>
