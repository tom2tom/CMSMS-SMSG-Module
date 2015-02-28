{* enter number for sms message template *}
<div class="cgsms_enternumber">
{if isset($error)}
  <div class="errormsg">{$error}</div>
{/if}
{if isset($message)}
  <div class="infomsg">{$message}</div>
{/if}

{$formstart}
<div class="row">
  <p class="leftcol">{$CGSMS->Lang('enter_mobile_number')}:</p>
  <p class="rightcol">
    <input type="text" name="{$actionid}cgsms_mobile" value="" size="14" maxlength="14"/>
  </p>
</div>
  <p class="leftcol"></p>
  <p class="rightcol">
    <input type="submit" name="{$actionid}cgsms_submit" value="{$CGSMS->Lang('send')}"/>
  </p>
</div>
{$formend}
</div>