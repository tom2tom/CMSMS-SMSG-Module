{$formstart}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('prompt_sms_hourly_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}hourlylimit" value="{$hourlylimit}" size="3" maxlength="3"/>
 </p>
</div>
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('prompt_sms_daily_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}dailylimit" value="{$dailylimit}" size="3" maxlength="3"/>
 </p>
</div>
{if isset($masterpass)}<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('master_password')}:</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}masterpw" value="{$masterpass}" size="20" maxlength="64"/>
 </p>
</div>{/if}
<br />
<div class="pageoverflow">
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
 </p>
</div>
{$formend}
