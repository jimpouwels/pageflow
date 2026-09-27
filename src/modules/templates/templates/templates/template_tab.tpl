<form id="template_form" name="template_form" method="post"
      action="{$backend_base_url}&template={$current_template_id}" enctype="multipart/form-data">
    <input type="hidden" name="action" id="action" value="update_template" />
    <div class="content_left_column">
        {$templates_list}
    </div>
    <div class="content_right_column">
        {$template_editor}
        {$template_var_migration}
        {$template_code_editor}
        {$template_code_viewer}
    </div>
</form>