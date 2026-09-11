<ul>
    {foreach from=$element_holders item=element_holder}
        <li><strong>{$element_holder.type}:</strong> {$element_holder.title}</li>
    {/foreach}
</ul>