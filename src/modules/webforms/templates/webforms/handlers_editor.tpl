<div class="webforms_editor_add_buttons">
    {foreach from=$handlers_add_buttons item=handlers_add_button}
        {$handlers_add_button}
    {/foreach}
</div>

<div class="selected_webforms">
    {foreach from=$selected_handlers item=handler}
        <div class="draggable_wrapper">
            <span class="draggable_id_holder displaynone">{$handler.id}</span>
            <div class="draggable_header">
                <div class="draggable_header_left">
                    {$text_resources[$handler.name_resource_identifier]}
                </div>
                <div class="draggable_header_right">
                    <div class="draggable_action_buttons">
                        <a href="#"
                           onclick="deleteFormHandler('{$handler.id}', '{$text_resources.webforms_delete_handler_confirm_message}'); return false;"
                           title="{$text_resources.webforms_delete_handler_link_title}">
                            <img src="/admin?file=/default/img/default_icons/delete_small.png"
                                 alt="{$text_resources.webforms_delete_handler_link_title}"
                                 title="{$text_resources.webforms_delete_handler_link_title}" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="draggable_body">
                <div id="collapsable_body_{$handler.id}" class="admin_form">
                    <div class="form_field_editor_wrapper">
                        <div class="admin_form_v2">
                            {foreach from=$handler.properties item=property}
                                {$property.field}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>