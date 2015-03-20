{* smsbroadcast template *}

<fieldset>
<legend>{$mod->Lang('smsbroadcast_sms_gateway')}</legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('username')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_username" size="20" value="{$smsbroadcast_username}"/>
  </p>
  <p class="pagetext">{$mod->Lang('password')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_password" size="20" value="{$smsbroadcast_password}"/>
  </p>
  <p class="pagetext">{$mod->Lang('from')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_from" size="20" maxlength="24" value="{$smsbroadcast_from}"/>
  </p>
</div>
</fieldset>
