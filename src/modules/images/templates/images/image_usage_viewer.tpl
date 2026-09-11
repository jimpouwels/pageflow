<ul>
    {foreach from=$element_holders item=element_holder}
        <li><strong>{$element_holder.type}:</strong> <a href="{$element_holder.url}" target="_blank">{$element_holder.title}</a></li>
    {/foreach}
</ul>