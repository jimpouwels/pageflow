<input type="hidden" name="template_id" id="template_id" value="{$id}" />
<div class="admin_form_v2">
    {$name_field}
    {$filename_field}
    {$scopes_field}
    <h2>{$text_resources['template_editor_variables']}</h2>
    {foreach from=$var_defs item=var_def}
        {$var_def}
    {/foreach}
</div>