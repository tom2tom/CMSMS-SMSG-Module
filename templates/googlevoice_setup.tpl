{* google voice setup *}

<fieldset>
<legend>{$mod->Lang('googlevoice_sms_gateway')}</legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('login')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}googlevoice_email" size="20" value="{$googlevoice_email}"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('password')}</p>
  <p class="pageinput">
    <input type="password" name="{$actionid}googlevoice_password" size="20" value="{$googlevoice_password}"/>
  </p>
</div>
</fieldset>