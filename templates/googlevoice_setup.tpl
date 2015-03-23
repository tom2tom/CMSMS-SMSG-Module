{* google voice setup *}
<fieldset>
<legend>{$mod->Lang('frame_title',$gatename)}</legend>
<div class="pageoverflow" style="margin-top:0;">
  <p class="pagetext" style="margin-top:0;">{$mod->Lang('login')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}googlevoice_email" size="20" value="{$googlevoice_email}"/>
  </p>
  <p class="pagetext">{$mod->Lang('password')}</p>
  <p class="pageinput">
    <input type="password" name="{$actionid}googlevoice_password" size="20" value="{$googlevoice_password}"/>
  </p>
</div>
</fieldset>
