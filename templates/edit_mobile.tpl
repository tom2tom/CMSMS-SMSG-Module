<h3>{$mod->Lang('addedit_mobile')}</h3>
{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('name')}</p> 
  <p class="pageinput">
    <input type="text" name="{$actionid}name" value="{$name}" size="25" maxlength="25" />
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('phone_number')}</p> 
  <p class="pageinput">
    <input type="text" name="{$actionid}mobile" value="{$mobile}" size="25" maxlength="25" />
  </p>
</div>
<br />
<div class="pageoverflow">
  <p class="pageinput">
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" />
  </p>
</div>
{$formend}
