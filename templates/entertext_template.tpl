{* default enter-text template *}
<div id="smsg_entertext">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}
{$formstart}
 <textarea name="{$actionid}smsg_smstext" id="smsg_entertext" rows="4" cols="40" onkeypress="smsg_entertext_onchange();">{$smstext}</textarea>
 <br /><br />
 <input type="submit" id="smsg_entertext_submit" name="{$actionid}smsg_submit" value="{$mod->Lang('submit')}" />
 <span id="smsg_charsleft">{$maxsmschars}</span>
{$formend}
</div>
<script type="text/javascript">
//<![CDATA[{literal}
var st = null, cl = null;
function smsg_entertext_onchange() {
 if(st == null) st = document.getElementById('smsg_entertext');
 var charsleft = {/literal}{$maxsmschars}{literal} - st.value.length;
 if(charsleft < 0) return false;
 if(cl == null) cl = document.getElementById('smsg_charsleft');
 cl.innerHTML = charsleft;
 return true;
}
//]]>{/literal}
</script>
