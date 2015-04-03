{* enter number for sms message template *}
<div class="cgsms_enternumber">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}

{$formstart}
 <div class="row">
  <p class="leftcol">{$CGSMS->Lang('enter_mobile_number')}:</p>
  <p class="rightcol">
   <input type="text" name="{$actionid}cgsms_mobile" value="" size="14" maxlength="14" />
  </p>
 </div>
 <br />
 <p class="rightcol">
  <input type="submit" name="{$actionid}cgsms_submit" value="{$CGSMS->Lang('send')}" />
 </p>
{$formend}
</div>
