.listing {
    margin-bottom: 20px;
}

.sitewide_page_drag_handle {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 3px;
    cursor: grab;
    text-align: center;
}

.sitewide_page_drag_handle:active {
    cursor: grabbing;
}

.sitewide_page_drag_handle span {
    display: block;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--color-gray-400, #9ca3af);
}

.sitewide_page_sortable_row_active {
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
}

.sitewide_pages_fieldset {
    width: fit-content;
}

#update_sitewide_pages_form .admin_form_field_v2 .admin_label_wrapper {
    display: none;
}

#update_sitewide_pages_form .admin_form_field_v2 .admin_field_wrapper {
    width: 100%;
    max-width: 100%;
}

#update_sitewide_pages_form .article-lookup-summary {
    margin-top: 10px;
    margin-left: 0;
}