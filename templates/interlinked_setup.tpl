{* interlinked template *}

<fieldset>
<legend>{$mod->Lang('interlinked_sms_gateway')}</legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('username')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}interlinked_username" size="20" value="{$interlinked_username}"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('password')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}interlinked_password" size="20" value="{$interlinked_password}"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('from')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}interlinked_from" size="12" maxlength="12" value="{$interlinked_from}"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('custom')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}interlinked_custom" size="20" maxlength="50" value="{$interlinked_custom}"/>
  </p>
</div>
</fieldset>