<form id="template_file_form" name="template_file_form" method="post"
      action="{$backend_base_url}&template_file={$current_template_file_id}" enctype="multipart/form-data">
    <input type="hidden" name="action" id="action" value="update_template_file" />
    <div class="content_left_column">
        {$template_files_list}
    </div>
    <div class="content_right_column">
        {$template_file_editor}
        {$template_var_migration}
        {$template_code_editor}
        {$template_code_viewer}
    </div>
</form>