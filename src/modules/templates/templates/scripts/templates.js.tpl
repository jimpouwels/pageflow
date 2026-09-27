$(document).ready(function () {
    function submitTemplateForm(action, form_id) {
        let $actionField = $('#' + form_id + ' #action');
        if ($actionField.length == 0) {
            alert("{$text_resources.templates_alert_action_error}");
        } else {
            $actionField.attr('value', action);
            let $template_form = $('#' + form_id);
            if ($template_form.length == 0) {
                alert("{$text_resources.templates_alert_save_error}");
            } else {
                $template_form.trigger('submit');
            }
        }
    }

    $('#add_template').on('click', function () {
        submitTemplateForm('add_template', 'template_add_form');
    });

    $('#update_template').on('click', function () {
        submitTemplateForm('update_template', 'template_form');
    });

    $('#reload_template').on('click', function () {
        submitTemplateForm('reload_template', 'template_form');
    });

    $('#delete_template').on('click', function () {
        confirmDialog("{$text_resources.templates_confirm_delete_template|escape:'javascript'}").then(function(confirmed) {
            if (confirmed) {
                submitTemplateForm('delete_template', 'template_form');
            }
        });
    });

    $('#update_template_variant').on('click', function () {
        submitTemplateForm('update_template_variant', 'template_variant_editor_form');
    });

    $('#add_template_variant').on('click', function () {
        submitTemplateForm('add_template_variant', 'template_variant_editor_form');
    });

    $('#delete_template_variant').on('click', function () {
        let $checkboxes = $('input[name^="template_variant_"][name$="_delete"]');
        if ($checkboxes.length > 0) {
            let $checked = false;
            $checkboxes.each(function () {
                if ($(this).prop('checked')) {
                    $checked = true;
                }
            });
            if (!$checked) {
                alert("{$text_resources.templates_alert_no_templates_selected}");
                return;
            }
        }
        confirmDialog("{$text_resources.templates_confirm_delete_template_variants|escape:'javascript'}").then(function(confirmed) {
            if (confirmed) {
                submitTemplateForm('delete_template_variants', 'template_variant_editor_form');
            }
        });
    });

});