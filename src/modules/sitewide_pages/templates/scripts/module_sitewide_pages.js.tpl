$(document).ready(function () {
    $('#update_sitewide_pages').on('click', () => {
        $('#action').attr('value', 'add_sitewide_page');
        $('#update_sitewide_pages_form').trigger('submit');
    });

    $('#remove_sitewide_pages').on('click', () => {
        confirmDialog("{$text_resources.sitewide_pages_confirm_delete|escape:'javascript'}").then(function(confirmed) {
            if (confirmed) {
                $('#action').attr('value', 'remove_sitewide_pages');
                $('#update_sitewide_pages_form').trigger('submit');
            }
        });
        return false;
    });

    initializeSitewidePagesSorting();
});

function updateSitewidePagesOrder($container) {
    var pageIds = [];
    $container.find('.sitewide_page_sortable_row').each(function () {
        var pageId = parseInt($(this).data('sitewide-page-id'), 10);
        if (!isNaN(pageId) && pageId > 0) {
            pageIds.push(pageId);
        }
    });
    $('#sitewide_pages_order').val(pageIds.join(','));
}

function initializeSitewidePagesSorting() {
    var $container = $('#sitewide_pages_sortable_items');
    if (!$container.length) {
        return;
    }

    var handle = '.sitewide_page_drag_handle';
    var dragSrc = null;
    var lastTarget = null;
    var lastBefore = null;

    $container.children('.sitewide_page_sortable_row').each(function () {
        var row = this;
        row.draggable = true;

        var mousedownTarget = null;
        row.addEventListener('mousedown', function (e) {
            mousedownTarget = e.target;
        });

        row.addEventListener('dragstart', function (e) {
            var fromHandle = mousedownTarget && $(mousedownTarget).closest(handle).length > 0;
            if (!fromHandle) {
                e.preventDefault();
                return;
            }
            e.stopPropagation();
            dragSrc = row;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
            requestAnimationFrame(function () { $(row).addClass('sitewide_page_sortable_row_active'); });
        });

        row.addEventListener('dragover', function (e) {
            if (!dragSrc || dragSrc === row) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            var rect = row.getBoundingClientRect();
            var before = e.clientY < rect.top + rect.height / 2;

            if (before && dragSrc.nextElementSibling === row) return;
            if (!before && row.nextElementSibling === dragSrc) return;
            if (row === lastTarget && before === lastBefore) return;
            lastTarget = row;
            lastBefore = before;

            $container[0].insertBefore(dragSrc, before ? row : row.nextElementSibling);
        });

        row.addEventListener('dragend', function () {
            $(row).removeClass('sitewide_page_sortable_row_active');
            dragSrc = null;
            lastTarget = null;
            lastBefore = null;
            updateSitewidePagesOrder($container);
            $('#action').attr('value', 'reorder_sitewide_pages');
            $('#update_sitewide_pages_form').trigger('submit');
        });
    });

    $container[0].addEventListener('dragover', function (e) {
        if (dragSrc) e.preventDefault();
    });

    updateSitewidePagesOrder($container);
}