<div class="collapsable_root_wrapper">
    <span class="collapsable_id_holder draggable_id_holder displaynone">{$id}</span>
    <div class="draggable_wrapper {$identifier}">
        <div class="draggable_header collapsable_header">
            <div class="collapsable_header_left draggable_header_left">
                <img src="{$icon_url}" alt="{$type}" />&nbsp;{$type}
            </div>
            <div class="draggable_header_right">
                {if $include_in_table_of_contents}
                    <div class="include_in_table_of_contents">
                        {$include_in_table_of_contents}
                    </div>
                {/if}
                <div class="template_picker">
                    {$template_picker}
                </div>
                <div class="draggable_action_buttons">
                    <a href="#" onclick="toggleElement('{$id}'); return false;"
                       title="{$text_resources.element_button_label_minimize}">
                        <img src="/admin?file=/default/img/default_icons/minimize.png" width="16px"
                             height="16px" alt="{$text_resources.element_button_label_minimize}"
                             title="{$text_resources.element_button_label_minimize}" />
                    </a>
                    <a href="#" onclick="toggleAllElements('{$id}'); return false;"
                       title="{$text_resources.element_button_label_minimize_all}">
                        <img src="/admin?file=/default/img/default_icons/minimize_all.png" width="16px"
                             height="16px" alt="{$text_resources.element_button_label_minimize_all}"
                             title="{$text_resources.element_button_label_minimize_all}" />
                    </a>
                    <a href="#"
                       onclick="deleteElement('{$id}','{$delete_element_form_id}', '{$text_resources.element_holder_delete_element_confirm_message}', '{$identifier}', '{$text_resources.element_holder_delete_container_confirm_message}', '{$text_resources.element_holder_delete_container_confirm_delete_children}', '{$text_resources.element_holder_delete_container_confirm_keep_children}'); return false;"
                       title="{$text_resources.element_button_label_delete}">
                        <img src="/admin?file=/default/img/default_icons/delete_small.png"
                             alt="{$text_resources.element_button_label_delete}"
                             title="{$text_resources.element_button_label_delete}" />
                    </a>
                </div>
            </div>
        </div>
        <div class="draggable_body">
            <p id="element_summary_text_{$id}" class="element_summary_text">{$summary_text}</p>
            <div id="collapsable_body_{$id}" class="admin_form">
                {$element_form}
            </div>
        </div>
    </div>
</div>