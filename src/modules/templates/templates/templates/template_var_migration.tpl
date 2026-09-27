<input type="hidden" name="is_migrating" value="true" />
{foreach from=$templates item=template}
    <h2>{$template.name}</h2>
    {foreach from=$template.vars item=var}
        <p>{$var.name}: {$var.value} {if $var.deleted}<strong>(DELETED)</strong>{/if}</p>
    {/foreach}
    {foreach from=$template.new_vars item=new_var}
        {$new_var}
    {/foreach}
    {if count($template.new_vars) == 0}
        <p><strong>{$text_resources['templates_no_migration_required']}</strong></p>
    {/if}
{/foreach}