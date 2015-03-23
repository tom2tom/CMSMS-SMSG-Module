{* clickatell setup *}
<fieldset>
<legend>{$mod->Lang('frame_title',$gatename)}</legend>
<div class="pageoverflow" style="margin-top:0;">
 <p class="pagetext" style="margin-top:0;">{$mod->Lang('username')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}ctell_username" size="24" value="{$ctell_username}"/>
 </p>
 <p class="pagetext">{$mod->Lang('password')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}ctell_password" size="24" value="{$ctell_password}"/>
 </p>
 <p class="pagetext">{$mod->Lang('apiid')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}ctell_apiid" size="10" value="{$ctell_apiid}"/>
 </p>
</div>
</fieldset>
