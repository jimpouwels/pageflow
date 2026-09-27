<ul>
    {foreach from=$templates item=template}
        {assign var=li_class value=""}
        {if $template.is_active}
            {assign var=li_class value="active"}
        {/if}
        <li class="{$li_class}">
            <a id="template_link"
               href="{$backend_base_url}&template={$template.id}">{$template.name}</a>
        </li>
    {/foreach}
</ul>
