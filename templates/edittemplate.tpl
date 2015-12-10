<h3>{if !empty($module_description)}{$module_description} - {/if}{$title}:</h3>
{if !empty($template_info)}
<div class="pageoverflow">{$template_info}</div>
{/if}
{$formstart}
{$hidden}
<div class="pageoverflow">
 <p class="pagetext">{$prompt_templatename}:</p>
 <p class="pageinput">{$templatename}</p>
 <p class="pagetext">{$prompt_template}:</p>
 <p class="pageinput">{$template}</p>
 <br />
 <p class="pageinput">{$submit}{$cancel}{if isset($apply)}{$apply}{/if}</p>
</div>
{$formend}
