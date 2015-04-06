<p>{$mod->Lang('info_smstest')}</p>
{$formstart}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('mobile_number')}:</p>
 <p class="pageinput"><input type="text" name="{$actionid}mobile" size="20" maxlength="20" /></p>
</div>
<br />
<div class="pageoverflow">
 <p class="pageinput"><input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" /></p>
</div>
{$formend}
