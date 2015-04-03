{* enter text template *}
<script type="text/javascript">
//<![CDATA[
var st = null, cl = null;
function cgsms_entertext_onchange()
{literal}{
  if(st == null) st = document.getElementById('cgsms_entertext_smstext');
  var charsleft = {/literal}{$maxsmschars}{literal} - st.value.length - 1;
  if(charsleft < 0) return false;
  if(cl == null) cl = document.getElementById('cgsms_entertext_charsleft');
  cl.innerHTML = charsleft;
  return true;
}{/literal}
//]]>
</script>

<div id="cgsms_entertext">
{if !empty($error)}<p class="errormsg">{$error}</p>{/if}
{if !empty($message)}<p class="infomsg">{$message}</p>{/if}

{$formstart}
 <textarea name="{$actionid}cgsms_smstext" id="cgsms_entertext_smstext" rows="4" cols="40" onkeypress="cgsms_entertext_onchange();">{$smstext}</textarea>
 <br /><br />
 <input type="submit" id="cgsms_entertext_submit" name="{$actionid}cgsms_submit" value="{$mod->Lang('submit')}" />
 <span id="cgsms_entertext_charsleft">{$maxsmschars}</span>
{$formend}
</div>
