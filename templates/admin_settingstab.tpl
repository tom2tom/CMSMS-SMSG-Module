<script type="text/javascript">
{literal}
if( jQuery ) {
jQuery(document).ready(function(){
  // setup the change callback.
  jQuery('#sms_gateway').change(function(){
    // get the selected class name
    jQuery('.sms_gateway_panel').hide();
    var val = jQuery('#sms_gateway').val();
    jQuery('#'+val).show();
    // 
  });
  
  // hide all the panels
  jQuery('.sms_gateway_panel').hide();

  var val = jQuery('#sms_gateway').val();
  // show only the selected one.
  jQuery('#'+val).show();
})
}
{/literal}
</script>

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

{foreach from=$objects key='classname' item='one'}
<div id="{$classname}" class="sms_gateway_panel" style="margin-top: 0.5em; margin-bottom: 0.5em;">
{$one.form}
</div>
{/foreach}

<div class="pageoverflow">
  <p class="pagetext"></p>
  <p class="pageinput">
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
  </p>
</div>
{$formend}