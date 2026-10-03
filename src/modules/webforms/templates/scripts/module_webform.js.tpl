// initialize event handlers
$(document).ready(function () {
    // add webform action button
    $('#add_webform').on('click', function () {
        $('#add_webform_action').attr('value', 'add_webform');
        $('#add_form_hidden').trigger('submit');
        return false;
    });

    // update webform action button
    $('#update_webform').on('click', function () {
        $('#action').attr('value', 'update_webform');
        $('#webform-editor-form').trigger('submit');
    });

    // delete webform action button
    $('#delete_webform').on('click', function () {
        confirmDialog("{$text_resources.webforms_confirm_delete_webform|escape:'javascript'}").then(function(confirmed) {
            if (confirmed) {
                $('#action').attr('value', 'delete_webform');
                $('#webform-editor-form').trigger('submit');
            }
        });
    });

    initializeWebformDragAndDrop();
});

// Initializes drag-and-drop reordering (by pressing the panel header, same as element
// holder editing) for both the form fields list and the form handlers list.
function initializeWebformDragAndDrop() {
    initDraggableWebformList($('.webforms_editor_form_fields'), '#draggable_order');
    initDraggableWebformList($('.selected_webforms'), '#webform_handlers_order');
}

function initDraggableWebformList($list, orderFieldSelector) {
    if (!$list.length) return;

    var cancelSelectors = 'input, textarea, select, button, a';
    var dragSrc = null;
    var placeholder = null;
    var lastTarget = null;
    var lastBefore = null;

    function updateOrder() {
        var ids = [];
        $list.children('.draggable_wrapper').each(function () {
            var id = $(this).find('.draggable_id_holder').first().text();
            if (id) ids.push(id);
        });
        $(orderFieldSelector).val(ids.join(','));
    }

    $list.children('.draggable_wrapper').each(function () {
        var el = this;
        el.draggable = true;

        var mousedownTarget = null;
        el.addEventListener('mousedown', function (e) {
            mousedownTarget = e.target;
        });

        el.addEventListener('dragstart', function (e) {
            var fromHeader = mousedownTarget && $(mousedownTarget).closest('.draggable_header').length > 0;
            var fromCancelTarget = mousedownTarget && $(mousedownTarget).closest(cancelSelectors).length > 0;
            if (!fromHeader || fromCancelTarget) {
                e.preventDefault();
                return;
            }
            e.stopPropagation();
            dragSrc = el;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');

            placeholder = document.createElement('div');
            placeholder.className = 'webform_drag_placeholder';
            placeholder.style.height = el.offsetHeight + 'px';
            requestAnimationFrame(function () {
                $list[0].insertBefore(placeholder, el);
                $(el).hide();
                $list.addClass('is-dragging');
            });
        });

        el.addEventListener('dragover', function (e) {
            if (!dragSrc || !placeholder) return;
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'move';

            var rect = el.getBoundingClientRect();
            var before = e.clientY < rect.top + rect.height / 2;
            if (el === lastTarget && before === lastBefore) return;
            lastTarget = el;
            lastBefore = before;

            $list[0].insertBefore(placeholder, before ? el : el.nextElementSibling);
        });

        el.addEventListener('dragend', function (e) {
            e.stopPropagation();
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.insertBefore(el, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
            $(el).show();
            placeholder = null;
            dragSrc = null;
            lastTarget = null;
            lastBefore = null;
            $list.removeClass('is-dragging');
            updateOrder();
        });
    });

    $list[0].addEventListener('dragover', function (e) {
        if (!dragSrc) return;
        e.preventDefault();
        if (placeholder && placeholder.parentNode !== $list[0]) {
            $list[0].appendChild(placeholder);
        }
    });

    // Initialize the hidden order field for first save, even before a drag action happens.
    updateOrder();
}

function addFormField(type) {
    $('#action').attr('value', 'add_' + type);
    $('#webform-editor-form').trigger('submit');
}

function deleteFormField(itemId, confirmMessage) {
    confirmDialog(confirmMessage).then(function(confirmed) {
        if (confirmed) {
            $('#action').attr('value', 'delete_form_item');
            $('#webform_item_to_delete').attr('value', itemId);
            $('#webform-editor-form').trigger('submit');
        }
    });
}

function addFormHandler(type) {
    $('#action').attr('value', 'add_handler_' + type);
    $('#webform-editor-form').trigger('submit');
}

function deleteFormHandler(handlerId, confirmMessage) {
    confirmDialog(confirmMessage).then(function(confirmed) {
        if (confirmed) {
            $('#action').attr('value', 'delete_form_handler');
            $('#webform_handler_to_delete').attr('value', handlerId);
            $('#webform-editor-form').trigger('submit');
        }
    });
}

function onCaptchaChanged(captchaKeyFieldClass) {
    $('.' + captchaKeyFieldClass).toggleClass('displaynone');
}