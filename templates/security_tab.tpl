{$formstart}
<div class="pageoverflow">
 <p class="pagetext">{$mod->Lang('prompt_hourly_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}hourlimit" value="{$hourlimit}" size="3" maxlength="3" />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_daily_limit')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}daylimit" value="{$daylimit}" size="3" maxlength="4" />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_log_sends')}:</p>
 <p class="pageinput">
  <input type="checkbox" name="{$actionid}logsends"{if $logsends} checked="checked"{/if} />
 </p>
 <p class="pagetext">{$mod->Lang('prompt_log_retain_days')}:</p>
 <p class="pageinput">
  <input type="text" name="{$actionid}logdays" value="{$logdays}" size="2" maxlength="3" />
 </p>
{if isset($masterpass)}<p class="pagetext">{$mod->Lang('prompt_master_password')}:</p>
 <p class="pageinput">
  <input type="password" name="{$actionid}masterpass" value="{$masterpass}" size="20" maxlength="64" />
 </p>{/if}
 <br />
 <p class="pageinput">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" />
 </p>
</div>
{$formend}
