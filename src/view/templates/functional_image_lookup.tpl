<div class="fimg-lookup">
    <input type="hidden" name="{$field_name}" id="{$field_name}" value="{$field_value}" data-element-id="{$element_id}" />
    <div class="fimg-lookup-preview{if $field_value} has-image{/if}"
         onclick="openFunctionalImagePicker('{$field_name}');" title="Kies functionele afbeelding">
        <img class="fimg-lookup-thumb" src="{if $field_value}/admin/fimage/{$field_value}{/if}" alt="" />
        <span class="fimg-lookup-placeholder">&#128247;</span>
        <button type="button" class="fimg-lookup-clear-btn"
                onclick="event.stopPropagation(); clearFunctionalImageSelection('{$field_name}');">&#10005;</button>
    </div>
</div>
