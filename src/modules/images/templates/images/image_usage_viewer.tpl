<ul class="image-usage-list">
    {foreach from=$element_holders item=element_holder}
        <li class="image-usage-list-item">
            <span class="image-usage-list-item-type">{$element_holder.type}</span>
            <a class="image-usage-list-item-link" href="{$element_holder.url}" target="_blank">{$element_holder.title}</a>
        </li>
    {foreachelse}
        <li class="image-usage-list-empty">{$image_usage_viewer_empty_text}</li>
    {/foreach}
</ul>