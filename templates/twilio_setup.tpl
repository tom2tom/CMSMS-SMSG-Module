{* twilio setup *}
<fieldset>
<legend>{$mod->Lang('frame_title',$gatename)}</legend>
<div class="pageoverflow" style="margin-top:0;">
 <p class="pagetext" style="margin-top:0;">{$mod->Lang('account')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}twilio_username" size="40" value="{$twilio_username}"/>
 </p>
 <p class="pagetext">{$mod->Lang('token')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}twilio_password" size="40" value="{$twilio_password}"/>
 </p>
 <p class="pagetext">{$mod->Lang('from')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}twilio_from" size="16" maxlength="16" value="{$twilio_from}"/>
 </p>
</div>
</fieldset>
