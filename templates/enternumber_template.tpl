{* default enter-number template *}
<div class="smsg_enternumber">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}
{$formstart}
{if !empty($gatename)}<input type="hidden" name="{$actionid}gatename" value="{$gatename}" />{/if}
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
