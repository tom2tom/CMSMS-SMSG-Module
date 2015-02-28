{if isset($mobiles)}
<table class="pagetable" border="0">
  <thead>
   <tr>
     <th>{$mod->Lang('id')}</th>
     <th>{$mod->Lang('name')}</th>
     <th>{$mod->Lang('number')}</th>
     <th class="pageicon"></th>
     <th class="pageicon"></th>
   </tr>
  </thead>
  <tbody>
  {foreach from=$mobiles item='mobile'}
    {cycle values="row1,row2" assign='rowclass'}
    <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
      <td>{$mobile.id}</td>
      <td>{$mobile.name}</td>
      <td>{$mobile.mobile}</td>
      <td>{$mobile.edit_link}</td>
      <td>{$mobile.del_link}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}
<div class="pageoptions">
  {$add_link}
</div>