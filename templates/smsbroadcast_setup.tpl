{* smsbroadcast template *}
<fieldset>
<legend>{$mod->Lang('frame_title',$gatename)}</legend>
<div class="pageoverflow" style="margin-top:0;">
  <p class="pagetext" style="margin-top:0;">{$mod->Lang('username')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_username" size="24" value="{$smsbroadcast_username}"/>
  </p>
  <p class="pagetext">{$mod->Lang('password')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_password" size="24" value="{$smsbroadcast_password}"/>
  </p>
  <p class="pagetext">{$mod->Lang('from')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}smsbroadcast_from" size="20" maxlength="24" value="{$smsbroadcast_from}"/>
  </p>
</div>
</fieldset>
