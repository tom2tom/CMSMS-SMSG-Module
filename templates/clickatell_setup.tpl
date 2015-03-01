{* clickatell setup *}
<fieldset>
<legend>{$mod->Lang('clickatell_title')}</legend>
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('username')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}ctell_username" size="20" value="{$ctell_username}"/>
 </p>
 <p class="pagetext">{$mod->Lang('password')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}ctell_password" size="20" value="{$ctell_password}"/>
 </p>
 <p class="pagetext">{$mod->Lang('apiid')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}ctell_apiid" size="10" value="{$ctell_apiid}"/>
 </p>
</div>
</fieldset>
