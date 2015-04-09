{* enter number for sms message template *}
<div class="smsg_enternumber">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}

{$formstart}
 <div class="row">
  <p class="leftcol">{$mod->Lang('enter_mobile_number')}:</p>
  <p class="rightcol">
   <input type="text" name="{$actionid}smsg_mobile" value="" size="14" maxlength="14" />
  </p>
 </div>
 <br />
 <p class="rightcol">
  <input type="submit" name="{$actionid}smsg_submit" value="{$mod->Lang('send')}" />
 </p>
{$formend}
</div>
