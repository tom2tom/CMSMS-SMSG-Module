{* twilio setup *}
<fieldset>
<legend>{$mod->Lang('twilio_title')}</legend>
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('account')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}account" size="30" value="{$account}"/>
 </p>
 <p class="pagetext">{$mod->Lang('token')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}token" size="30" value="{$token}"/>
 </p>
</div>
</fieldset>