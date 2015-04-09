{* enter text template *}
<div id="smsg_entertext">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}

{$formstart}
 <textarea name="{$actionid}smsg_smstext" id="smsg_entertext_smstext" rows="4" cols="40" onkeypress="smsg_entertext_onchange();">{$smstext}</textarea>
 <br /><br />
 <input type="submit" id="smsg_entertext_submit" name="{$actionid}smsg_submit" value="{$mod->Lang('submit')}" />
 <span id="smsg_entertext_charsleft">{$maxsmschars}</span>
{$formend}
</div>
{$js}
