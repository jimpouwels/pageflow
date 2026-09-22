<input type="hidden" id="{$add_element_form_id}" name="{$add_element_form_id}" value="" />
<input type="hidden" id="{$edit_element_holder_id}" name="{$edit_element_holder_id}" value="{$current_article_id}" />
<input type="hidden" id="element_holder_version" name="element_holder_version" value="{$element_holder_version}" />
<input type="hidden" id="{$delete_element_form_id}" name="{$delete_element_form_id}" value="" />
<input type="hidden" id="draggable_order" name="draggable_order" value="" />
<input type="hidden" id="element_container_assignments" name="element_container_assignments" value="" />
<input type="hidden" id="delete_container_children" name="delete_container_children" value="" />
<input type="hidden" id="{$action_form_id}" name="{$action_form_id}" value="" />
<input type="hidden" id="article_preview_url" name="article_preview_url" value="{$url}?mode=preview" />

<div class="admin_form_v2">
    {$name_field}
    {$title_field}
    {$seo_title_field}
    {$url_title_field}
    {$url_field}
    {$description_field}
    {$published_field}
    {$publication_date_field}
    {$sort_date_field}
    {$target_pages_field}
    {$parent_article_field}
    {if count($child_articles) > 0}
        <div class="admin_form_field_v2">
            <div class="admin_label_wrapper">
                <label class="admin_label">{$text_resources.article_editor_child_articles_label}</label>
            </div>
            <div class="admin_field_wrapper">
                <ul style="margin: 0; padding: 0 0 0 15px;">
                    {foreach from=$child_articles item=child_article}
                        <li>
                            <i><a title="{$child_article.title}" href="{$child_article.url}">{$child_article.title}</a></i>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
    {$comment_forms_field}
    {$template_field}
    {$image_picker_field}
    {$wallpaper_picker_field}
    
    <div class="metadata-fields-section collapsed">
        <h3 class="metadata-fields-toggle">
            Metadata
            <span class="toggle-icon"></span>
        </h3>
        <div class="metadata-fields-content">
            {foreach from=$metadata_fields item=metadata_field}
                {$metadata_field}
            {/foreach}
        </div>
    </div>
</div>
