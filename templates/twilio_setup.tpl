{* twilio setup *}
<fieldset>
<legend>{$mod->Lang('twilio_title')}</legend>
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('account')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}twilio_username" size="36" value="{$twilio_username}"/>
 </p>
 <p class="pagetext">{$mod->Lang('token')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}twilio_password" size="36" value="{$twilio_password}"/>
 </p>
 <p class="pagetext">{$mod->Lang('from')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}twilio_from" size="16" maxlength="16" value="{$twilio_from}"/>
 </p>
</div>
</fieldset>