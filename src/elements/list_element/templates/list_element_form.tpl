<div class="admin_form_v2">
    <input type="hidden" value="" name="element{$id}_add_item" id="element{$id}_add_item" />
    <input type="hidden" value="{$list_item_order}" name="element_{$id}_list_item_order" id="element_{$id}_list_item_order" />
    {$title_field}
    <div class="panel-title">Items</div>
    <div id="list_element_{$id}_items" class="list-element-sortable-items" data-order-field-id="element_{$id}_list_item_order">
        {if count($list_items) > 0}
            {foreach from=$list_items item=list_item}
                <div class="list_element_values list-element-sortable-item" data-list-item-id="{$list_item.id}">
                    <div class="list_element_item_drag_handle" title="Reorder" aria-label="Reorder">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="list_element_item_functional_image">{$list_item.functional_image_lookup}</div>
                    <div class="list_element_item_value_field">{$list_item.item_text_field}</div>
                    <div class="list_element_item_value_delete_field">{$list_item.delete_field}</div>
                </div>
            {/foreach}
        {/if}
    </div>
    {if count($list_items) == 0}
        <em class="list_element_empty_state">{$text_resources.list_element_message_no_list_items}</em>
    {/if}
    {$add_item_button}
</div>

{* Shared functional image picker modal *}
<div id="fimg-picker-modal-{$id}" class="fimg-picker-modal" style="display:none;">
    <div class="fimg-picker-backdrop" onclick="closeFunctionalImagePicker();"></div>
    <div class="fimg-picker-content">
        <div class="fimg-picker-header">
            <h3>Functionele afbeelding kiezen</h3>
            <button type="button" class="close-btn" onclick="closeFunctionalImagePicker();">&times;</button>
        </div>
        <div class="fimg-picker-search">
            <input type="text" id="fimg-picker-search-input-{$id}" placeholder="Zoeken..." oninput="filterFunctionalImagePicker(this.value)" />
        </div>
        <div class="fimg-picker-tree" id="fimg-picker-tree-{$id}">
            {include file="list_element/templates/fimg_picker_tree.tpl" folders=$fimg_picker_root_folders images=$fimg_picker_root_images}
            {if empty($fimg_picker_root_folders) && empty($fimg_picker_root_images)}
                <div class="fimg-empty-state">Geen functionele afbeeldingen beschikbaar.</div>
            {/if}
        </div>
    </div>
</div>

<script type="text/javascript">
var _fimgPickerContext = null;
var _fimgPickerModal = null;
var _fimgAllImages = {$all_functional_images|@json_encode};

// Register this list element's modal so openFunctionalImagePicker can find it by field name
if (!window._fimgModalRegistry) { window._fimgModalRegistry = {}; }
(function() {
    var modalId = 'fimg-picker-modal-{$id}';
    var $form = $('#element{$id}_add_item').closest('.admin_form_v2');
    $form.find('.fimg-lookup input[type=hidden]').each(function() {
        window._fimgModalRegistry[this.name] = modalId;
    });
})();

function openFunctionalImagePicker(fieldName) {
    _fimgPickerContext = fieldName;
    var modalId = window._fimgModalRegistry && window._fimgModalRegistry[fieldName];
    var $modal = modalId ? $('#' + modalId) : $();
    _fimgPickerModal = $modal;
    $modal.find('[id^="fimg-picker-search-input"]').val('');
    filterFunctionalImagePicker('');
    if ($modal.parent()[0] !== document.body) {
        $modal.appendTo(document.body);
    }
    $modal.show();
}

function closeFunctionalImagePicker() {
    _fimgPickerContext = null;
    if (_fimgPickerModal) {
        _fimgPickerModal.hide();
        _fimgPickerModal = null;
    }
}

function selectFunctionalImage(imageId, imageTitle) {
    if (!_fimgPickerContext) return;
    var $hidden = $('#' + _fimgPickerContext);
    $hidden.val(imageId);
    var $preview = $hidden.closest('.fimg-lookup').find('.fimg-lookup-preview');
    $preview.find('.fimg-lookup-thumb').attr('src', '/admin/fimage/' + imageId);
    $preview.addClass('has-image');
    closeFunctionalImagePicker();
}

function clearFunctionalImageSelection(fieldName) {
    var $hidden = $('#' + fieldName);
    $hidden.val('');
    var $preview = $hidden.closest('.fimg-lookup').find('.fimg-lookup-preview');
    $preview.find('.fimg-lookup-thumb').attr('src', '');
    $preview.removeClass('has-image');
}

function filterFunctionalImagePicker(query) {
    var $tree = _fimgPickerModal ? _fimgPickerModal.find('.fimg-picker-tree') : $('.fimg-picker-tree').first();
    var q = query.toLowerCase();
    if (!q) {
        $tree.find('.fimg-picker-item').show();
        $tree.find('.fimg-folder').show();
        return;
    }
    $tree.find('.fimg-folder').hide();
    $tree.find('.fimg-picker-item').each(function () {
        var title = $(this).data('title').toLowerCase();
        if (title.indexOf(q) !== -1) {
            $(this).show();
            $(this).parents('.fimg-folder').show().addClass('open');
        } else {
            $(this).hide();
        }
    });
}
</script>
