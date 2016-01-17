{if !empty($template_info)}
<div class="pageoverflow">{$template_info}</div>
{/if}
{$formstart}
{$hidden}
<div class="pageoverflow">
 <p class="pagetext">{$title_name}:</p>
 {$name}
 <p class="pagetext">{$title_content}:</p>
 {$content}
 <br /><br />
 {$submit} {$cancel}{if isset($apply)} {$apply}{/if}
</div>
{$formend}
