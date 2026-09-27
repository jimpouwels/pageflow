{if count($template_variants) > 0}
        <table class="listing template_listing" cellspacing="0" cellpadding="5" border="0">
            <colgroup width="350px"></colgroup>
            <colgroup width="100px"></colgroup>
            <thead>
            <tr>
                <th>{$text_resources.template_variants_list_name_column}</th>
                <th class="center">{$text_resources.template_variants_list_delete_column}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$template_variants item=template}
                <tr>
                    <td><a href="{$backend_base_url}&template_variant={$template.id}"
                           title="{$template.name}">{$template.name}</a></td>
                    <td class="center last">
                        {$template.delete_checkbox}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        {$information_message}
    {/if}
