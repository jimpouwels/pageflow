// Modern Confirm Dialog
// Replaces the native confirm() with a custom styled dialog
function showConfirm(message, options) {
    return new Promise(function(resolve) {
        var opts = options || {};
        var title = opts.title || 'Bevestiging';
        var confirmText = opts.confirmText || 'Bevestigen';
        var cancelText = opts.cancelText || 'Annuleren';
        var secondaryText = opts.secondaryText || null;
        var isDanger = opts.danger || false;
        
        // Set dialog content
        $('#confirm-dialog-title').text(title);
        $('#confirm-dialog-message').text(message);
        $('#confirm-dialog-confirm').text(confirmText);
        $('#confirm-dialog-cancel').text(cancelText);
        if (secondaryText) {
            $('#confirm-dialog-secondary').text(secondaryText).show();
        } else {
            $('#confirm-dialog-secondary').hide();
        }
        
        // Add/remove danger class
        if (isDanger) {
            $('#confirm-dialog-confirm').addClass('danger');
        } else {
            $('#confirm-dialog-confirm').removeClass('danger');
        }
        
        // Show dialog. Stop any in-progress fade animation first, since this same
        // dialog element/queue may still be finishing a previous showConfirm() call.
        $('#confirm-dialog').stop(true, true).css({ display: 'flex', opacity: 0 }).animate({ opacity: 1 }, 150);
        
        // Store handlers so we can remove them later
        var close = function(result) {
            $('#confirm-dialog').stop(true, true).animate({ opacity: 0 }, 150, function() { $(this).hide(); });
            $('#confirm-dialog-confirm').off('click', confirmHandler);
            $('#confirm-dialog-secondary').off('click', secondaryHandler);
            $('#confirm-dialog-cancel').off('click', cancelHandler);
            $('.confirm-dialog-backdrop').off('click', cancelHandler);
            resolve(result);
        };
        var confirmHandler = function() { close(true); };
        var secondaryHandler = function() { close('secondary'); };
        var cancelHandler = function() { close(false); };
        
        // Attach event listeners
        $('#confirm-dialog-confirm').on('click', confirmHandler);
        $('#confirm-dialog-secondary').on('click', secondaryHandler);
        $('#confirm-dialog-cancel').on('click', cancelHandler);
        $('.confirm-dialog-backdrop').on('click', cancelHandler);
        
        // Keyboard shortcuts
        var keyHandler = function(e) {
            if (e.key === 'Escape') {
                cancelHandler();
            } else if (e.key === 'Enter') {
                confirmHandler();
            }
        };
        
        $(document).one('keydown', keyHandler);
    });
}

// Convenience function that mimics the old confirm() behavior for easy migration
function confirmDialog(message) {
    return showConfirm(message, { danger: true });
}

// handles the 'add element' click in the navigation menu
function addElement(elementTypeId, errorMessage) {
    var $inputField = $('#add_element_type_id');
    if ($inputField.length == 0) {
        alert(errorMessage);
        return false;
    } else {
        $inputField.attr('value', elementTypeId);
    }
    var $editorForm = $('#element_holder_form_id');
    $editorForm.trigger('submit');
}

// Global variable to store insert position
var elementInsertPosition = null;

// Global variable to store the target container id (null means top level)
var elementInsertTargetContainerId = null;

// Shows the element selector modal at a specific position (top level)
function showElementSelector(position) {
    elementInsertPosition = position;
    elementInsertTargetContainerId = null;
    $('#element-selector-modal').fadeIn(200);
}

// Shows the element selector modal to insert an element inside a given container
function showElementSelectorForContainer(containerId, position) {
    elementInsertPosition = position;
    elementInsertTargetContainerId = containerId;
    $('#element-selector-modal').fadeIn(200);
}

// Hides the element selector modal
function hideElementSelector() {
    $('#element-selector-modal').fadeOut(200);
    elementInsertPosition = null;
    elementInsertTargetContainerId = null;
}

// Inserts element at the stored position
function insertElementAtPosition(elementTypeId) {
    var $inputField = $('#add_element_type_id');
    if ($inputField.length == 0) {
        alert('Fout: Kan element niet toevoegen');
        return false;
    }
    
    $inputField.attr('value', elementTypeId);
    
    // Set insert position
    var $positionField = $('#element_insert_position');
    if ($positionField.length == 0) {
        // Create hidden field if it doesn't exist
        $('<input>').attr({
            type: 'hidden',
            id: 'element_insert_position',
            name: 'element_insert_position',
            value: elementInsertPosition
        }).appendTo('#element_holder_form_id');
    } else {
        $positionField.attr('value', elementInsertPosition);
    }

    // Set target container id, if inserting inside an element container
    var $targetContainerField = $('#target_container_id');
    if ($targetContainerField.length == 0) {
        $('<input>').attr({
            type: 'hidden',
            id: 'target_container_id',
            name: 'target_container_id',
            value: elementInsertTargetContainerId || ''
        }).appendTo('#element_holder_form_id');
    } else {
        $targetContainerField.attr('value', elementInsertTargetContainerId || '');
    }
    
    hideElementSelector();
    $('#element_holder_form_id').trigger('submit');
}

$(document).ready(function() {
});

// Saves the selected element holder back to the link in the parent window
function submitSelectionBackToOpener(backRef, backValue, backClickId) {
    var $backField = window.opener.$('#' + backRef);
    if ($backField.length > 0) {
        $backField.attr('value', backValue);
        // Delay the click and window close to ensure value is set
        setTimeout(function() {
            window.opener.$('#' + backClickId).trigger('click');
            setTimeout(function() {
                window.close();
            }, 200);
        }, 50);
    } else {
        alert('Fout: Kan niet opgeslagen worden, waarschijnlijk is het hoofdscherm gesloten');
    }
}

// handles the 'delete element' click
function deleteElement(elementId, formFieldId, confirmMessage, identifier, containerConfirmMessage, deleteChildrenText, keepChildrenText) {
    var $inputField = $('#' + formFieldId);
    if ($inputField.length == 0) {
        alert('Fout: Kan element niet verwijderen');
        return false;
    } else {
        $inputField.attr('value', elementId);
    }
    if (identifier === 'element_container_element') {
        // Single 3-way choice: cancel, delete the container only (children become
        // unparented), or delete the container together with its children.
        // Previously this chained two separate confirm dialogs that shared the same
        // DOM element/animation queue, which could race and require deleting twice.
        showConfirm(containerConfirmMessage, {
            confirmText: deleteChildrenText,
            secondaryText: keepChildrenText,
            cancelText: 'Annuleren',
            danger: true
        }).then(function(result) {
            if (result === false) {
                return;
            }
            var $deleteChildrenField = $('#delete_container_children');
            if ($deleteChildrenField.length > 0) {
                $deleteChildrenField.attr('value', result === true ? '1' : '0');
            }
            $('#action').attr('value', 'update_element_holder');
            $('#element_holder_form_id').trigger('submit');
        });
    } else {
        confirmDialog(confirmMessage).then(function(confirmed) {
            if (!confirmed) {
                return;
            }
            $('#action').attr('value', 'update_element_holder');
            $('#element_holder_form_id').trigger('submit');
        });
    }
}

// elements visibility
$(document).ready(function () {
    getAllElements().each(function () {
        var rootNode = $(this);
        var elementId = getElementIdFromElementNode(rootNode);
        if (!isVisible(elementId)) {
            hideElement(elementId);
        }
        var elementHeader = findElementHeader(rootNode);
        elementHeader.on('click', function (e) {
            /*
                currentTarget == the node where the click listener is attached to ('.collapsable_header')
                target == the node that captured the event and bubbled it up the tree
                if there was a child element that captured and bubbled the event, don't toggle (unless it's the left part of the header).
            */
            if (e.currentTarget !== e.target && !e.target.className.includes('collapsable_header_left')) {
                return;
            }
            toggleElement(elementId);
        });
    });
});

function toggleElement(elementId) {
    if (isVisible(elementId)) {
        hideElement(elementId);
    } else {
        showElement(elementId);
    }
}

function toggleAllElements(elementId) {
    if (isVisible(elementId)) {
        hideElements();
    } else {
        showElements();
    }
}

function hideElements() {
    getAllElements().each(function () {
        hideElement(getElementIdFromElementNode($(this)));
    });
}

function showElements() {
    getAllElements().each(function () {
        showElement(getElementIdFromElementNode($(this)));
    });
}

function hideElement(elementId) {
    $('#collapsable_body_' + elementId).hide();
    $('#element_summary_text_' + elementId).show();
    localStorage.setItem("sa_element_visible_" + elementId, "false");
}

function showElement(elementId) {
    $('#collapsable_body_' + elementId).show();
    $('#element_summary_text_' + elementId).hide();
    localStorage.setItem("sa_element_visible_" + elementId, "true");
}

function isVisible(elementId) {
    return localStorage.getItem('sa_element_visible_' + elementId) != 'false';
}

function getAllElements() {
    return $('.collapsable_root_wrapper');
}

function getElementIdFromElementNode(elementNode) {
    return elementNode.children('.collapsable_id_holder').text()
}

function findElementHeader(elementNode) {
    return elementNode.children('.draggable_wrapper').children('.collapsable_header');
}

// initializes sortable elements using native HTML5 drag-and-drop
// Supports multiple independent lists (the top-level element holder list plus any
// nested element-container child lists), including dragging an element from one
// list into another (e.g. into / out of an element container).
$(document).ready(function () {
    var $allContainers = $(".draggable_items");
    if (!$allContainers.length) return;

    var cancelSelectors = '.rich-text-content, .rich-text-toolbar, input, textarea, select, button, a';
    var dragSrc = null;
    var placeholder = null;
    var lastTarget = null;
    var lastBefore = null;

    var buttonHtml = '<div class="element-insert-button" data-insert-position="POS">' +
        '<button type="button" class="insert-btn" onclick="ONCLICK; return false;" title="Element invoegen">' +
        '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
        '<path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
        '</svg></button>' +
        '<div class="insert-indicator"><div class="insert-line"></div>' +
        '<div class="insert-arrow">' +
        '<svg width="12" height="12" viewBox="0 0 12 12" fill="none">' +
        '<path d="M6 2L6 10M6 10L3 7M6 10L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
        '</svg></div></div></div>';

    // Only the top-level element holder list and nested element-container child
    // lists get insert buttons; plain lists (e.g. webform fields) are left alone.
    function isElementList($list) {
        return $list.attr('id') === 'element_container' || $list.hasClass('element_container_child_list');
    }

    function buttonHtmlFor($list, position) {
        var containerId = $list.data('container-id');
        var onclick = containerId ? "showElementSelectorForContainer('" + containerId + "'," + position + ")" : 'showElementSelector(' + position + ')';
        return buttonHtml.replace(/POS/g, position).replace('ONCLICK', onclick);
    }

    function rebuildInsertButtonsFor($list) {
        if (!isElementList($list)) return;
        // Keep the empty/centered-plus styling in sync with whether the list
        // actually still has elements (e.g. after dragging the first element
        // into, or the last element out of, an element container).
        $list.toggleClass('empty-container', $list.children('.collapsable_root_wrapper').length === 0);
        $list.children('.element-insert-button').remove();
        $list.prepend(buttonHtmlFor($list, 0));
        var idx = 0;
        $list.children('.collapsable_root_wrapper').each(function () {
            idx++;
            $(this).after(buttonHtmlFor($list, idx));
        });
    }

    function rebuildInsertButtons() {
        $allContainers.each(function () {
            rebuildInsertButtonsFor($(this));
        });
    }

    // Rebuilds the container-assignment map (element id -> containing element id)
    // from the current DOM structure of the nested element-container child lists.
    function updateContainerAssignments() {
        var $assignmentsField = $('#element_container_assignments');
        if (!$assignmentsField.length) return;
        var assignments = {};

        // Explicitly mark top-level elements as null so moving an element out of
        // a container is persisted and not inferred as "missing".
        $('#element_container').children('.collapsable_root_wrapper').each(function () {
            var topLevelId = $(this).find('.draggable_id_holder').first().text();
            if (topLevelId) assignments[topLevelId] = null;
        });

        $('.element_container_child_list').each(function () {
            var containerId = $(this).data('container-id');
            $(this).children('.collapsable_root_wrapper').each(function () {
                var childId = $(this).find('.draggable_id_holder').first().text();
                if (childId) assignments[childId] = containerId;
            });
        });
        $assignmentsField.attr('value', JSON.stringify(assignments));
    }

    function updateOrder() {
        var idString = '';
        $allContainers.each(function () {
            $(this).children('.collapsable_root_wrapper').each(function () {
                var id = $(this).find('.draggable_id_holder').first().text();
                if (!id) return;
                if (idString !== '') idString += ',';
                idString += id;
            });
        });
        var $order_field = $('#draggable_order');
        if ($order_field.length > 0) $order_field.attr('value', idString);
        updateContainerAssignments();
        rebuildInsertButtons();
    }

    // Initialize hidden fields for first save, even before a drag action happens.
    updateOrder();

    // Finds the nearest non-insert-button sibling after a node within its parent list.
    function nextItem(node) {
        var s = node.nextElementSibling;
        while (s && s.classList.contains('element-insert-button')) s = s.nextElementSibling;
        return s;
    }

    $allContainers.children('.collapsable_root_wrapper').each(function () {
        var el = this;
        el.draggable = true;

        var mousedownTarget = null;
        var mousedownX = 0;
        var mousedownY = 0;
        el.addEventListener('mousedown', function (e) {
            // Stop here so a mousedown inside a nested element (e.g. within an
            // element container) doesn't also get recorded by the container's
            // own (ancestor) mousedown listener.
            e.stopPropagation();
            mousedownTarget = e.target;
            mousedownX = e.clientX;
            mousedownY = e.clientY;
        });

        el.addEventListener('dragstart', function (e) {
            var fromHeader = mousedownTarget && $(mousedownTarget).closest('.draggable_header').length > 0;
            var fromCancelTarget = mousedownTarget && $(mousedownTarget).closest(cancelSelectors).length > 0;
            if (!fromHeader || fromCancelTarget) {
                e.preventDefault();
                return;
            }
            // Stop bubbling so an ancestor element (e.g. the element container
            // this element lives in) doesn't also treat this as its own drag start.
            e.stopPropagation();
            dragSrc = el;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');

            // Custom drag ghost: styled bar with icon + element name (mirrors the header)
            var ghost = document.createElement('div');
            ghost.className = 'element-drag-ghost';
            ghost.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:' + el.offsetWidth + 'px;';
            var headerLeft = el.querySelector('.draggable_header_left');
            if (headerLeft) {
                ghost.appendChild(headerLeft.cloneNode(true));
            }
            document.body.appendChild(ghost);
            var rect = el.getBoundingClientRect();
            var offsetX = mousedownX - rect.left;
            var offsetY = Math.min(mousedownY - rect.top, ghost.offsetHeight - 1);
            e.dataTransfer.setDragImage(ghost, offsetX, offsetY);
            setTimeout(function () { document.body.removeChild(ghost); }, 0);

            // Placeholder shown where element will land
            placeholder = document.createElement('div');
            placeholder.className = 'element-drag-placeholder';
            placeholder.style.height = el.offsetHeight + 'px';

            var $ownList = $(el).closest('.draggable_items');
            requestAnimationFrame(function () {
                $ownList[0].insertBefore(placeholder, el);
                $(el).hide();
                $allContainers.addClass('is-dragging');
            });
        });

        el.addEventListener('dragover', function (e) {
            if (!dragSrc || !placeholder) return;
            // Elements can only be dropped into element lists (top-level or a
            // container's child list), never into unrelated draggable lists (e.g. webforms).
            var $targetList = $(el).closest('.draggable_items');
            if (!isElementList($targetList)) return;
            e.preventDefault();
            // Stop bubbling so an ancestor element's own dragover handler (e.g. the
            // container this element lives in) doesn't immediately reposition the
            // placeholder back out to its own (outer) list.
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'move';

            var rect = el.getBoundingClientRect();
            var before = e.clientY < rect.top + rect.height / 2;

            if (before && nextItem(placeholder) === el) return;
            if (!before && nextItem(el) === placeholder) return;
            if (el === lastTarget && before === lastBefore) return;
            lastTarget = el;
            lastBefore = before;

            // Insert after el: find the first non-insert-button sibling after el
            var insertRef = before ? el : nextItem(el);
            $targetList[0].insertBefore(placeholder, insertRef || null);
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
            $allContainers.removeClass('is-dragging');
            updateOrder();
        });
    });

    $allContainers.each(function () {
        var list = this;
        list.addEventListener('dragover', function (e) {
            if (!dragSrc) return;
            var $list = $(list);
            if (!isElementList($list)) return;
            e.preventDefault();
            // Stop bubbling so an ancestor list (e.g. the top-level list, when this
            // is a nested container child list) doesn't also reposition the placeholder.
            e.stopPropagation();
            // Allow drops anywhere inside the list (also when hovering the plus button).
            if (!list.contains(e.target)) return;
            if (placeholder && placeholder.parentNode !== list) {
                list.appendChild(placeholder);
            } else if (placeholder && !placeholder.parentNode) {
                list.appendChild(placeholder);
            }
        });
    });

    $allContainers.each(function () {
        var list = this;
        list.addEventListener('drop', function () {
            updateOrder();
        });
    });

});

// Auto-scroll the page while dragging near the top or bottom edge
(function () {
    var HEADER_HEIGHT = 56; // fixed header height in px
    var SCROLL_ZONE = 80;   // additional px below header / above bottom to start scrolling
    var MAX_SPEED = 15;     // px per animation frame at full proximity
    var scrollSpeed = 0;
    var rafId = null;

    function scrollLoop() {
        if (scrollSpeed !== 0) {
            window.scrollBy(0, scrollSpeed);
            rafId = requestAnimationFrame(scrollLoop);
        } else {
            rafId = null;
        }
    }

    document.addEventListener('dragover', function (e) {
        var y = e.clientY;
        var vh = window.innerHeight;
        var topEdge = HEADER_HEIGHT + SCROLL_ZONE;
        var bottomEdge = vh - SCROLL_ZONE;

        if (y < topEdge) {
            scrollSpeed = -Math.round(MAX_SPEED * (1 - y / topEdge));
        } else if (y > bottomEdge) {
            scrollSpeed = Math.round(MAX_SPEED * (1 - (vh - y) / SCROLL_ZONE));
        } else {
            scrollSpeed = 0;
        }

        if (scrollSpeed !== 0 && !rafId) {
            rafId = requestAnimationFrame(scrollLoop);
        }
    });

    function stopScroll() {
        scrollSpeed = 0;
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    }

    document.addEventListener('dragend', stopScroll);
    document.addEventListener('drop', stopScroll);
})();

// starts sliding in the notification bar
$(document).ready(function () {
    // slides in the notification bar
    var slider = $("#notification-slider");
    var originalMarginTop = slider.css('margin-top');
    slider.animate({
        marginTop: "0"
    }, 1000);
    setTimeout(function () {
        slider.animate({
            marginTop: originalMarginTop
        }, 1000);
    }, 3000);
});

// handles add link
function addLink() {
    var $elementHolderForm = $('#element_holder_form_id');
    if ($elementHolderForm.length == 0) {
        alert('Fout: Kan link niet toevoegen');
        return false;
    } else {
        var $actionField = $('#action');
        $actionField.attr('value', 'add_link');
        $elementHolderForm.trigger('submit');
    }
}

// makes sure every field that should be able to
// contain a link is prepared
var lastFocussedField = undefined;
$(document).ready(function () {
    $('.linkable').each(function () {
        $(this).on('focus', function () {
            lastFocussedField = document.getElementById($(this).attr("id"));
        });
    });
});

// handles put link
function putLink(linkId) {
    var errorMessage = "Fout: U heeft geen tekst geselecteerd, of het is niet toegestaan aan het huidige veld een link toe te voegen.";
    if (lastFocussedField == undefined) {
        alert(errorMessage);
    } else {
        var len = lastFocussedField.value.length;
        var start = lastFocussedField.selectionStart;
        var end = lastFocussedField.selectionEnd;
        var value = lastFocussedField.value;
        var selectedText = value.substring(start, end);
        if (selectedText != undefined && selectedText.length > 0) {
            newText = "[LINK C=\"" + ($('#link_' + linkId + '_code').val()) + "\"]" + selectedText + "[/LINK]";
            lastFocussedField.value = lastFocussedField.value.substring(0, start) + newText + lastFocussedField.value.substring(end, len);
        } else {
            alert(errorMessage);
        }
    }
}

// deletes the selected link target for a link
function deleteLink(linkId) {
    confirmDialog("Weet u zeker dat u dit linkdoel wilt verwijderen?").then(function(confirmed) {
        if (confirmed) {
            $('#delete_link_target').attr('value', linkId);
            $('#action').attr('value', 'update_element_holder');
            var $editorForm = $('#element_holder_form_id');
            $editorForm.trigger('submit');
        }
    });
    return false;
}

// horizontal dropdown menu
$(document).ready(function () {
    $('.module-group').on('mouseover', function () {
        var $submenu = $(this).find('.submenu');
        $submenu.show();
    });
    $('#menu > li').on('mouseout', function () {
        var $submenu = $(this).find('.submenu');
        $submenu.hide();
    });
});

// element holder scrollbehaviour 
function storeScrollPosition(elementHolderId, scrollPosition) {
    var ratio = document.body.scrollHeight > 0 ? scrollPosition / document.body.scrollHeight : 0;
    localStorage.setItem("sa_element_holder_scroll_position", JSON.stringify({
        elementHolderId: elementHolderId,
        ratio: ratio
    }));
}

function getScrollRatio(elementHolderId) {
    var scrollPos = JSON.parse(localStorage.getItem("sa_element_holder_scroll_position"));
    if (!scrollPos) {
        return 0;
    }
    if (scrollPos.elementHolderId == elementHolderId) {
        return scrollPos.ratio || 0;
    } else {
        storeScrollPosition(elementHolderId, 0);
        return 0;
    }
}

$(document).ready(function () {
    var elementHolderId = $('#element_holder_id').attr('value');
    if (elementHolderId) {
        var scrollRatio = getScrollRatio(elementHolderId);
        if (scrollRatio > 0) {
            setTimeout(function () {
                window.scrollTo(0, Math.round(scrollRatio * document.body.scrollHeight));
            }, 100);
            setTimeout(function () {
                window.scrollTo(0, Math.round(scrollRatio * document.body.scrollHeight));
            }, 500);
        }
    }

    $(window).on('scroll', function () {
        if (!elementHolderId) return;
        clearTimeout($.data(this, 'scrollTimer'));
        $.data(this, 'scrollTimer', setTimeout(function () {
            storeScrollPosition(elementHolderId, window.pageYOffset || document.documentElement.scrollTop);
        }, 100));
    });

    $('#element_holder_form_id').on('submit', function () {
        if (elementHolderId) {
            storeScrollPosition(elementHolderId, window.pageYOffset || document.documentElement.scrollTop);
        }
    });
});

// Photo album element selected images
function addImage(elementId, imageId) {
    let imagesContainer = $('#photo_album_element_' + elementId + '_selected_images');
    let entityId = imagesContainer.data('entity-id');
    let updateEndpoint = imagesContainer.data('update-endpoint');
    
    $.ajax({
        url: updateEndpoint,
        type: 'PUT',
        data: JSON.stringify({
            'id': entityId,
            'image': imageId
        }),
        success: function(response) {
            if (response && response.element_holder_version) {
                $(document).trigger('element-holder-version-synced', response.element_holder_version);
            }
            updateSelectedImages(elementId);
        },
        error: function(xhr, status, error) {
            console.log(xhr, status, error);
        }
    });
}

function updateSelectedImages(elementId) {
    let imagesContainer = $('#photo_album_element_' + elementId + '_selected_images');
    let getEndpoint = imagesContainer.data('get-endpoint');
    
    $.ajax({
        url: getEndpoint,
        type: 'GET',
        success: function(response) {
            if (response.length === 0) {
                imagesContainer.empty();
                return;
            }
            
            imagesContainer.html('<div class="selected-images-grid"></div>');
            let gridContainer = imagesContainer.find('.selected-images-grid');
            
            response.forEach(result => {
                let card = $('<div class="selected-image-card" data-image-id="' + result.id + '"></div>');
                
                // Drag handle with 4-way arrows icon
                let dragHandle = $('<div class="selected-image-drag-handle"></div>');
                dragHandle.html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="3" x2="12" y2="21" stroke-width="2"/><line x1="3" y1="12" x2="21" y2="12" stroke-width="2"/><path d="M12 3l-3 3m3-3l3 3m-3 15l-3-3m3 3l3-3M3 12l3-3m-3 3l3 3m15-3l-3-3m3 3l-3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                card.append(dragHandle);
                
                // Delete button with X icon
                let deleteBtn = $('<div class="selected-image-delete"></div>');
                deleteBtn.html('<svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                deleteBtn.on('click', function(e) {
                    e.stopPropagation();
                    confirmDialog('Weet u zeker dat u deze afbeelding wilt verwijderen?').then(function(confirmed) {
                        if (confirmed) {
                            deleteImage(elementId, result.id);
                        }
                    });
                });
                card.append(deleteBtn);
                
                // Thumbnail
                card.append('<div class="selected-image-thumb"><img src="' + result.url + '" alt="' + (result.alternative_text || '') + '" /></div>');
                
                // Info
                let info = $('<div class="selected-image-info"></div>');
                info.append('<div class="selected-image-title">' + (result.title || 'Untitled') + '</div>');
                info.append('<div class="selected-image-alt">' + (result.alternative_text || 'Geen alt-tekst') + '</div>');
                card.append(info);
                
                gridContainer.append(card);
            });
            
            // Initialize drag and drop functionality
            initImageDragDrop(elementId);
        },
        error: function(xhr, status, error) {
            console.log(xhr, status, error);
        }
    });
}

function deleteImage(elementId, imageId) {
    let imagesContainer = $('#photo_album_element_' + elementId + '_selected_images');
    let entityId = imagesContainer.data('entity-id');
    let deleteEndpoint = imagesContainer.data('delete-endpoint');
    
    $.ajax({
        url: deleteEndpoint,
        type: 'DELETE',
        data: JSON.stringify({
            'id': entityId,
            'image': imageId
        }),
        success: function(response) {
            if (response && response.element_holder_version) {
                $(document).trigger('element-holder-version-synced', response.element_holder_version);
            }
            updateSelectedImages(elementId);
        },
        error: function(xhr, status, error) {
            console.log(xhr, status, error);
        }
    });
}

function initImageDragDrop(elementId) {
    let gridContainer = $('#photo_album_element_' + elementId + '_selected_images .selected-images-grid');
    
    if (gridContainer.length === 0) {
        return;
    }
    
    let draggedCard = null;
    let placeholder = null;
    let offsetX, offsetY;
    let lastTargetCardElement = null; // Store DOM element for hysteresis
    let lastTargetBefore = false; // Store position for hysteresis
    
    gridContainer.find('.selected-image-card').each(function() {
        let card = $(this);
        let dragHandle = card.find('.selected-image-drag-handle');
        
        dragHandle.on('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            draggedCard = card;
            
            // For fixed positioning, use viewport coordinates (clientX/Y) not document coordinates (pageX/Y)
            let rect = card[0].getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            
            // Store original dimensions
            let width = card.outerWidth();
            let height = card.outerHeight();
            
            // Create placeholder
            placeholder = $('<div class="selected-image-card-placeholder"></div>');
            placeholder.css({
                width: width + 'px',
                height: height + 'px'
            });
            
            // Insert placeholder where card was
            card.before(placeholder);
            
            // Lift card out of normal flow and make it follow mouse
            card.css({
                position: 'fixed',
                width: width + 'px',
                height: height + 'px',
                left: (e.clientX - offsetX) + 'px',
                top: (e.clientY - offsetY) + 'px',
                zIndex: '10000',
                margin: '0',
                pointerEvents: 'none',
                opacity: '0.95',
                transform: 'scale(1.05) rotate(2deg)',
                boxShadow: '0 12px 40px rgba(0, 0, 0, 0.4)',
                transition: 'none'
            });
            
            $(document).on('mousemove.imagedrag', function(e) {
                if (!draggedCard) return;
                
                // Move card with mouse
                draggedCard.css({
                    left: (e.clientX - offsetX) + 'px',
                    top: (e.clientY - offsetY) + 'px'
                });
                
                // Helper: calculate distance from mouse to element center
                function distanceToCenter(element) {
                    let rect = element.getBoundingClientRect();
                    let dx = e.clientX - (rect.left + rect.width / 2);
                    let dy = e.clientY - (rect.top + rect.height / 2);
                    return Math.sqrt(dx * dx + dy * dy);
                }
                
                let allCards = gridContainer.find('.selected-image-card').not(draggedCard);
                let targetCard = null;
                let targetBefore = false;
                
                // Check if mouse is directly over any card
                allCards.each(function() {
                    let rect = this.getBoundingClientRect();
                    
                    if (e.clientX >= rect.left && e.clientX <= rect.right &&
                        e.clientY >= rect.top && e.clientY <= rect.bottom) {
                        
                        targetCard = $(this);
                        let relativeX = (e.clientX - rect.left) / rect.width;
                        
                        // Hysteresis: wider thresholds for same card to prevent flickering
                        if (lastTargetCardElement === this) {
                            targetBefore = lastTargetBefore ? relativeX < 0.65 : relativeX < 0.35;
                        } else {
                            targetBefore = relativeX < 0.5;
                        }
                        
                        lastTargetCardElement = this;
                        lastTargetBefore = targetBefore;
                        return false; // Break
                    }
                });
                
                // If no direct hit, find closest card (but only use if significantly closer than placeholder)
                if (!targetCard) {
                    let minDist = Infinity;
                    let closest = null;
                    let closestBefore = false;
                    
                    allCards.each(function() {
                        let dist = distanceToCenter(this);
                        if (dist < minDist) {
                            minDist = dist;
                            closest = this;
                            let rect = this.getBoundingClientRect();
                            let dx = e.clientX - (rect.left + rect.width / 2);
                            let dy = e.clientY - (rect.top + rect.height / 2);
                            closestBefore = Math.abs(dy) < rect.height ? dx < 0 : dy < 0;
                        }
                    });
                    
                    // Only use closest card if it's 30px+ closer than placeholder
                    if (closest && minDist < distanceToCenter(placeholder[0]) - 30) {
                        targetCard = $(closest);
                        targetBefore = closestBefore;
                    }
                }
                
                // Move placeholder if needed
                if (targetCard) {
                    let sibling = targetBefore ? placeholder.next()[0] : placeholder.prev()[0];
                    if (sibling !== targetCard[0]) {
                        targetBefore ? targetCard.before(placeholder) : targetCard.after(placeholder);
                    }
                }
            });
            
            $(document).on('mouseup.imagedrag', function() {
                if (draggedCard) {
                    // Reset card styles
                    draggedCard.css({
                        position: '',
                        width: '',
                        height: '',
                        left: '',
                        top: '',
                        zIndex: '',
                        margin: '',
                        pointerEvents: '',
                        opacity: '',
                        transform: '',
                        boxShadow: '',
                        transition: ''
                    });
                    
                    // Put card back in place of placeholder
                    placeholder.replaceWith(draggedCard);
                    
                    // Cleanup
                    draggedCard = null;
                    placeholder = null;
                    lastTargetCardElement = null;
                    lastTargetBefore = false;
                    
                    $(document).off('mousemove.imagedrag');
                    $(document).off('mouseup.imagedrag');
                    
                    // Save order
                    saveImageOrder(elementId);
                }
            });
        });
    });
}

function saveImageOrder(elementId) {
    let imagesContainer = $('#photo_album_element_' + elementId + '_selected_images');
    let entityId = imagesContainer.data('entity-id');
    let gridContainer = imagesContainer.find('.selected-images-grid');
    
    let imageIds = [];
    gridContainer.find('.selected-image-card').each(function() {
        imageIds.push(parseInt($(this).data('image-id')));
    });
    
    $.ajax({
        url: '/admin/api/photo_album_element/reorder_images',
        type: 'POST',
        data: JSON.stringify({
            'id': entityId,
            'imageIds': imageIds
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response && response.element_holder_version) {
                $(document).trigger('element-holder-version-synced', response.element_holder_version);
            }
            // Order saved successfully
        },
        error: function(xhr, status, error) {
            console.log('Error saving order:', xhr, status, error);
        }
    });
}

// Image selector modal functions
var imageSearchTimeout = null;
var imageSelectMode = 'single'; // 'single' or 'multiple'
var currentImageContext = null;
var currentFieldName = null;

function openImageSelector(contextId, multiple) {
    currentImageContext = contextId;
    imageSelectMode = multiple ? 'multiple' : 'single';
    
    // Get field name from global variable (only set in single mode)
    if (!multiple) {
        currentFieldName = window['imageSelector_fieldName_' + contextId];
    }
    
    $('#image-selector-modal-' + contextId).fadeIn(200);
    
    // Focus on search input
    setTimeout(function() {
        $('#image-search-' + contextId)[0].focus();
    }, 250);
    
    // Setup search event
    $('#image-search-' + contextId).off('input').on('input', function() {
        var keyword = $(this).val();
        clearTimeout(imageSearchTimeout);
        
        if (keyword.length > 0) {
            imageSearchTimeout = setTimeout(function() {
                searchImages(contextId, keyword);
            }, 300);
        } else {
            $('#image-selector-grid-' + contextId).html('<div class="image-selector-loading">Start met typen om afbeeldingen te zoeken...</div>');
        }
    });
    
    // Setup escape key handler
    $(document).off('keydown.imageSelector').on('keydown.imageSelector', function(e) {
        if (e.key === 'Escape') {
            closeImageSelector(contextId);
        }
    });
}

function closeImageSelector(contextId) {
    $('#image-selector-modal-' + contextId).fadeOut(200);
    $('#image-search-' + contextId).val('');
    $('#image-selector-grid-' + contextId).html('<div class="image-selector-loading">Start met typen om afbeeldingen te zoeken...</div>');
    // Remove escape key handler
    $(document).off('keydown.imageSelector');
}

function searchImages(contextId, keyword) {
    var gridContainer = $('#image-selector-grid-' + contextId);
    gridContainer.html('<div class="ajax-spinner"><span></span></div>');

    if (imageSelectMode === 'multiple') {
        var getEndpoint = $('#photo_album_element_' + contextId + '_selected_images').data('get-endpoint');
        $.ajax({
            url: getEndpoint,
            method: 'GET',
            success: function(images) {
                var selectedImageIds = images.map(img => img.id);
                performImageSearch(contextId, keyword, selectedImageIds, gridContainer);
            },
            error: function() {
                performImageSearch(contextId, keyword, [], gridContainer);
            }
        });
    } else {
        performImageSearch(contextId, keyword, [], gridContainer);
    }
}

function performImageSearch(contextId, keyword, selectedImageIds, gridContainer) {
    $.ajax({
        url: '/admin/api/image/search?keyword=' + encodeURIComponent(keyword),
        method: 'GET',
        success: function(response) {
            if (response.length === 0) {
                gridContainer.html('<div class="image-selector-loading">Geen afbeeldingen gevonden.</div>');
                return;
            }

            gridContainer.empty();
            response.forEach(function(image) {
                var isSelected = selectedImageIds.includes(image.id);

                var item = $('<div class="image-selector-item' + (isSelected ? ' selected' : '') + '" data-image-id="' + image.id + '"></div>');
                item.append('<div class="image-selector-checkmark"><svg viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12"></polyline></svg></div>');
                item.append('<div class="image-selector-thumb"><img src="' + image.url + '" alt="' + (image.alternative_text || '') + '" /></div>');
                item.append('<div class="image-selector-info"><div class="image-selector-title">' + (image.title || 'Untitled') + '</div><div class="image-selector-alt">' + (image.alternative_text || 'Geen alt-tekst') + '</div></div>');

                item.on('click', function() {
                    selectImage(contextId, image, $(this));
                });

                gridContainer.append(item);
            });
        },
        error: function(xhr, status, error) {
            gridContainer.html('<div class="image-selector-loading">Fout bij het zoeken: ' + error + '</div>');
            console.error(status, error);
        }
    });
}

function selectImage(contextId, image, element) {
    if (imageSelectMode === 'single') {
        // Get endpoint and entity ID from data attribute
        let $field = $('#' + currentFieldName);
        let entityId = $field.data('entity-id');
        let updateEndpoint = $field.data('update-endpoint');
        
        // Single selection - save via REST and update preview
        $.ajax({
            url: updateEndpoint,
            type: 'PUT',
            data: JSON.stringify({
                'id': entityId,
                'image': image.id
            }),
            success: function(response) {
                if (response && response.element_holder_version) {
                    $(document).trigger('element-holder-version-synced', response.element_holder_version);
                }
                $('#' + currentFieldName).val(image.id);
                updateSingleImagePreview(currentFieldName, image);
                closeImageSelector(contextId);
            },
            error: function(xhr, status, error) {
                console.error('Error saving image:', status, error);
            }
        });
    } else {
        // Multiple selection - toggle selection
        if (element.hasClass('selected')) {
            element.removeClass('selected');
            deleteImage(contextId, image.id);
        } else {
            element.addClass('selected');
            addImage(contextId, image.id);
        }
    }
}

// Update single image preview (for both element and article)
function updateSingleImagePreview(fieldName, image) {
    let previewContainer = $('#' + fieldName + '_preview');
    let $field = $('#' + fieldName);
    let contextId = $field.data('context-id');
    
    // If image is just an ID (number/string), we need to fetch it
    if (typeof image === 'number' || (typeof image === 'string' && !isNaN(image))) {
        let imageId = image;
        if (!imageId) {
            previewContainer.empty();
            return;
        }
        
        // Get endpoint from data attribute
        var getEndpoint = $field.data('get-endpoint');
        
        // Fetch image details
        $.ajax({
            url: getEndpoint,
            method: 'GET',
            success: function(response) {
                if (response) {
                    updateSingleImagePreview(fieldName, response);
                } else {
                    previewContainer.empty();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching image:', status, error);
                previewContainer.empty();
            }
        });
        return;
    }
    
    // If no image data, clear preview
    if (!image) {
        previewContainer.empty();
        return;
    }
    
    // Build preview card
    let card = $('<div class=\"single-image-preview-card\"></div>');
    
    // Delete button
    let deleteBtn = $('<div class=\"single-image-preview-delete\"></div>');
    deleteBtn.html('<svg viewBox=\"0 0 24 24\" fill=\"none\"><path d=\"M18 6L6 18M6 6l12 12\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>');
    deleteBtn.on('click', function(e) {
        e.stopPropagation();
        
        // Get endpoint and entity ID from data attribute
        let $field = $('#' + fieldName);
        let entityId = $field.data('entity-id');
        let deleteEndpoint = $field.data('delete-endpoint');
        
        // Delete via REST API
        $.ajax({
            url: deleteEndpoint,
            type: 'DELETE',
            data: JSON.stringify({
                'id': entityId
            }),
            success: function(response) {
                if (response && response.element_holder_version) {
                    $(document).trigger('element-holder-version-synced', response.element_holder_version);
                }
                $('#' + fieldName).val('');
                previewContainer.empty();
            },
            error: function(xhr, status, error) {
                console.error('Error deleting image:', status, error);
            }
        });
    });
    card.append(deleteBtn);
    
    // Thumbnail
    card.append('<div class=\"single-image-preview-thumb\"><img src=\"' + image.url + '\" alt=\"' + (image.alternative_text || '') + '\" /></div>');
    
    // Info
    let info = $('<div class=\"single-image-preview-info\"></div>');
    info.append('<div class=\"single-image-preview-title\">' + (image.title || 'Untitled') + '</div>');
    info.append('<div class=\"single-image-preview-alt\">' + (image.alternative_text || 'Geen alt-tekst') + '</div>');
    card.append(info);
    
    previewContainer.html(card);
}

// Auto-dismiss notification bar after 2.5 seconds
$(function() {
    var $notification = $('.notification-holder');
    if ($notification.length > 0) {
        setTimeout(function() {
            $notification.addClass('hiding');
            setTimeout(function() {
                $notification.remove();
            }, 500);
        }, 2500);
    }
});

// =====================================================================
// LINK PICKER MODAL
// Global API: openLinkPickerModal(function(link) { link.id, .title, .url })
// =====================================================================
var linkPickerCallback = null;
var linkPickerTree = null;
var linkPickerSearchTimeout = null;

$(document).ready(function () {
    initLinkPickerModal();
});

function openLinkPickerModal(onSelect) {
    linkPickerCallback = onSelect;
    $('#link-picker-modal').css({ display: 'flex', opacity: 0 }).animate({ opacity: 1 }, 150);
    $('#link-picker-search').val('').focus();

    loadLinkPickerTree();
}

function loadLinkPickerTree() {
    $('#link-picker-body').html('<div class="link-picker-loading"><div class="ajax-spinner"><span></span></div></div>');
    $.ajax({
        url: '/admin/api/link/tree',
        method: 'GET',
        success: function (tree) {
            linkPickerTree = tree;
            renderLinkPickerTree(tree);
        },
        error: function () {
            console.error('[LinkPicker] tree load failed');
            $('#link-picker-body').html('<div class="link-picker-empty">Laden mislukt</div>');
        }
    });
}

function renderLinkPickerTree(tree) {
    var html = '';
    if (tree.folders.length === 0 && tree.links.length === 0) {
        html = '<div class="link-picker-empty">Geen links beschikbaar</div>';
    } else {
        html += renderPickerFolders(tree.folders);
        html += renderPickerLinks(tree.links);
    }
    $('#link-picker-body').html(html);
    bindLinkPickerEvents();
}

function bindLinkPickerEvents() {
    $('#link-picker-body').off('click.lp').on('click.lp', '.link-picker-folder-header', function () {
        $(this).find('.link-picker-folder-toggle').toggleClass('open');
        $(this).next('.link-picker-folder-children').slideToggle(150);
    }).on('click.lp', '.link-picker-link-item', function () {
        selectLinkFromPicker(
            $(this).data('link-id'),
            $(this).data('link-title'),
            $(this).data('link-url')
        );
    });
}

function renderPickerFolders(folders) {
    var html = '';
    folders.forEach(function (folder) {
        html += '<div class="link-picker-folder">';
        html += '<div class="link-picker-folder-header">';
        html += '<span class="link-picker-folder-toggle">&#9658;</span>';
        html += '<span class="link-picker-folder-icon">&#128193;</span>';
        html += '<span class="link-picker-folder-name">' + lpEscapeHtml(folder.name) + '</span>';
        html += '</div>';
        html += '<div class="link-picker-folder-children" style="display:none;">';
        html += renderPickerFolders(folder.subFolders);
        html += renderPickerLinks(folder.links);
        html += '</div>';
        html += '</div>';
    });
    return html;
}

function renderPickerLinks(links) {
    var html = '';
    links.forEach(function (link) {
        html += '<div class="link-picker-link-item"'
            + ' data-link-id="' + link.id + '"'
            + ' data-link-title="' + lpEscapeAttr(link.name) + '"'
            + ' data-link-url="' + lpEscapeAttr(link.url || '') + '">';
        html += '<span class="link-picker-link-title">' + lpEscapeHtml(link.name) + '</span>';
        if (link.url) {
            html += '<span class="link-picker-link-url">' + lpEscapeHtml(link.url) + '</span>';
        }
        html += '</div>';
    });
    return html;
}

function filterLinkPickerSearch(keyword) {
    if (!linkPickerTree) {
        return;
    }
    keyword = keyword.toLowerCase().trim();
    if (!keyword) {
        renderLinkPickerTree(linkPickerTree);
        return;
    }
    var all = lpCollectAllLinks(linkPickerTree.folders, linkPickerTree.links);
    var filtered = all.filter(function (l) {
        return l.name.toLowerCase().indexOf(keyword) !== -1
            || (l.url && l.url.toLowerCase().indexOf(keyword) !== -1);
    });
    var html = filtered.length
        ? renderPickerLinks(filtered)
        : '<div class="link-picker-empty">Geen resultaten</div>';
    $('#link-picker-body').html(html);
    bindLinkPickerEvents();
}

function lpCollectAllLinks(folders, rootLinks) {
    var result = rootLinks.slice();
    folders.forEach(function (f) {
        result = result.concat(f.links);
        result = result.concat(lpCollectAllLinks(f.subFolders, []));
    });
    return result;
}

function selectLinkFromPicker(id, title, url) {
    closeLinkPickerModal();
    if (typeof linkPickerCallback === 'function') {
        linkPickerCallback({ id: id, name: title, title: title, url: url || '' });
    }
    linkPickerCallback = null;
}

function closeLinkPickerModal() {
    $('#link-picker-modal').animate({ opacity: 0 }, 150, function () { $(this).hide(); });
}

function initLinkPickerModal() {
    $('#link-picker-close, #link-picker-cancel').on('click', closeLinkPickerModal);
    $('.link-picker-backdrop').on('click', closeLinkPickerModal);
    $(document).on('keydown.linkpicker', function (e) {
        if (e.key === 'Escape' && $('#link-picker-modal').is(':visible')) {
            e.stopPropagation();
            closeLinkPickerModal();
        }
    });
    $('#link-picker-search').on('input', function () {
        var val = $(this).val();
        clearTimeout(linkPickerSearchTimeout);
        linkPickerSearchTimeout = setTimeout(function () {
            filterLinkPickerSearch(val);
        }, 200);
    });
}

function lpEscapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function lpEscapeAttr(str) {
    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
}
