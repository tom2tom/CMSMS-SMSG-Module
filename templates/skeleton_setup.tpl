{* adjust to suit specific-gateway parameters NB all such templates are in play at the same time *}
<fieldset>
<legend>{$mod->Lang('frame_title',$gatename)}</legend>
<div class="pageoverflow" style="margin-top:0;">
 <p class="pagetext" style="margin-top:0;">{$mod->Lang('account')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}skeleton_username" size="40" value="{$skeleton_username}" />
 </p>
 <p class="pagetext">{$mod->Lang('token')}</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}skeleton_password" size="40" value="{$skeleton_password}" />
 </p>
 <p class="pagetext">{$mod->Lang('from')}</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}skeleton_from" size="16" maxlength="16" value="{$skeleton_from}" />
 </p>
</div>
</fieldset>
