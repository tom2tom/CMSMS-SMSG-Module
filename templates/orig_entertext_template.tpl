{* enter text template *}
<script type="text/javascript">
var numchars='{$maxsmschars}';
{literal}
function cgsms_entertext_onchange()
{
  text = document.getElementById('cgsms_entertext_smstext').value;
  charsleft = numchars - text.length - 1;
  if( charsleft < 0 ) return false;
  document.getElementById('cgsms_entertext_charsleft').innerHTML = charsleft;
}
{/literal}
</script>

<div id="cgsms_entertext">
{if isset($error)}
  <div class="errormsg">{$error}</div>
{/if}
{if isset($message)}
  <div class="infomsg">{$message}</div>
{/if}

{$formstart}
<textarea name="{$actionid}cgsms_smstext" id="cgsms_entertext_smstext" rows="4" cols="40" onkeypress="cgsms_entertext_onchange();">{$smstext}</textarea>
<br />
<input type="submit" id="cgsms_entertext_submit" name="{$actionid}cgsms_submit" value="{$mod->Lang('submit')}" />
<span id="cgsms_entertext_charsleft">{$maxsmschars}</span>
{$formend}
</div>
