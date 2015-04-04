{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('reporting_url')}:</p>
  <p class="pageinput">{$reporturl}</p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('selected_gateway')}:</p>
  <p class="pageinput">
    <select id="sms_gateway" name="{$actionid}sms_gateway"> 
     {html_options options=$gatewaynames selected=$sms_gateway}
    </select>
  </p>
</div>

{foreach from=$objects key=alias item=one}
<div id="{$alias}" class="sms_gateway_panel" style="margin-top:0.5em;margin-bottom:0.5em;">
{$one.form}
</div>
{/foreach}
<br />
<div class="pageoverflow">
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
 </p>
</div>
{$formend}
