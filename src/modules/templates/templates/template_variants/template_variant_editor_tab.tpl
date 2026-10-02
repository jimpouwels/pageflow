<form id="template_variant_editor_form" name="template_variant_editor_form" method="post"
      action="{$backend_base_url}&template_variant={$current_template_variant_id}" enctype="multipart/form-data">
    <input type="hidden" name="action" id="action" value="update_template_variant" />
    <input type="hidden" name="template_variant_id" id="template_variant_id" value="{$current_template_variant_id}" />
    <div class="content_left_column">
        {$scope_selector}
    </div>
    <div class="content_right_column">
        {if isset($template_variant_editor)}
            {if isset($back_url)}
                <a href="{$back_url}" class="back-link">&larr; {$text_resources.templates_back_to_list}</a>
            {/if}
            {$template_variant_editor}
            {$template_variant_var_editor}
        {elseif isset($template_variant_list)}
            {$template_variant_list}
        {/if}
    </div>
</form>