/* IMAGES MODULE LAYOUT */
.images-module-wrapper {
}

.images-editor-wrapper {
    margin-top: var(--spacing-lg, 16px);
}

/* IMAGE EDITOR: SIZE DISPLAY */
.image-size-display {
    background: var(--color-gray-100, #f3f4f6);
    border-color: var(--color-gray-200, #e5e7eb);
    color: var(--color-gray-500, #6b7280);
    cursor: default;
    pointer-events: none;
    user-select: none;
}

/* IMAGE EDITOR: CENTER BUTTONS */
.crop-center-buttons {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.crop-center-btn {
    flex: 1;
    padding: 5px 10px;
    font-size: 12px;
    background: var(--color-white, #fff);
    border: 1px solid var(--color-gray-300, #d1d5db);
    border-radius: 4px;
    cursor: pointer;
    color: var(--color-gray-700, #374151);
    transition: background 0.15s, border-color 0.15s;
}

.crop-center-btn:hover {
    background: var(--color-gray-50, #f9fafb);
    border-color: var(--color-primary, #2271b1);
    color: var(--color-primary, #2271b1);
}

/* IMAGE MODULE SEARCH BAR */
.image_search.panel {
    max-width: 420px;
}

.image-module-search-wrapper {
    position: relative;
    max-width: 420px;
}

.image-module-search-input-row {
    display: flex;
    align-items: center;
}

.image-module-search-input {
    width: 100%;
    padding: 6px 10px;
    font-size: 13px;
    border: 1px solid var(--color-gray-300, #d1d5db);
    border-radius: 6px;
    background: var(--color-white, #fff);
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.15s;
}

.image-module-search-input:focus {
    border-color: var(--color-primary, #2271b1);
    box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.12);
}

.image-module-search-dropdown {
    position: fixed;
    z-index: 9999;
    background: var(--color-white, #fff);
    border: 1px solid var(--color-gray-200, #e5e7eb);
    border-radius: 6px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    overflow-y: auto;
    min-width: 380px;
    max-width: 460px;
}

.image-module-search-results {
    /* results container */
}

.image-module-search-placeholder {
    padding: 12px 14px;
    color: var(--color-gray-500, #6b7280);
    font-size: 13px;
}

.image-module-search-result-item {
    display: flex;
    align-items: center;
    border-bottom: 1px solid var(--color-gray-100, #f3f4f6);
    background: var(--color-white, #fff);
    box-sizing: border-box;
}

.image-module-search-result-item:last-child {
    border-bottom: none;
}

.image-module-search-result-item:hover {
    background: var(--color-gray-50, #f9fafb);
}

.image-module-search-result-link {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    padding: 8px 12px;
    border: none;
    background: transparent;
    text-align: left;
    cursor: pointer;
    min-width: 0;
}

.image-module-search-result-info {
    min-width: 0;
}

.image-module-search-result-delete {
    flex-shrink: 0;
    padding: 6px 12px;
    border: none;
    background: transparent;
    color: var(--color-gray-400, #9ca3af);
    font-size: 14px;
    cursor: pointer;
    line-height: 1;
}

.image-module-search-result-delete:hover {
    color: var(--color-danger, #dc2626);
}

.image-module-search-result-thumb {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    object-fit: contain;
    border-radius: 3px;
    background: var(--color-gray-100, #f3f4f6);
}

.image-module-search-result-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-gray-900, #111827);
}

.image-module-search-result-alt {
    font-size: 12px;
    color: var(--color-gray-500, #6b7280);
    margin-top: 2px;
}


.image_editor_wrapper .admin_label_wrapper {
    flex: 0 0 180px;
}

.image_editor_wrapper .admin_field {
    width: 100%;
    max-width: 500px;
}

/* IMAGE METADATA */
.image_meta {
    /* Flows in content_right_column */
    margin-top: var(--spacing-lg, 16px);
}

/* FORM OVERRIDES FOR IMAGES MODULE */
.image_search .admin_form,
.image_editor .admin_form {
    padding: 0;
}

/* IMAGE IMPORT FORM */
.image-import-form {
    max-width: 600px;
}

.image-import-form .import-form-content {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 8px;
    padding: var(--spacing-xl);
}

.image-import-form .import-help-text {
    margin: 0 0 var(--spacing-lg) 0;
    color: var(--color-gray-600);
    font-size: 14px;
    line-height: 1.6;
}

.image-import-form .import-fields {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.image-import-form .admin_form_field_v2 {
    margin: 0;
}

/* Style file input as button (import + image editor) */
.image-import-form input[type="file"],
#image-editor-form input[type="file"] {
    display: inline-block;
    padding: 8px 16px;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.image-import-form input[type="file"]:hover,
#image-editor-form input[type="file"]:hover {
    background: var(--color-primary-dark);
}

.image-import-form input[type="file"]::file-selector-button,
#image-editor-form input[type="file"]::file-selector-button {
    display: none;
}

/* IMAGE USAGE VIEWER */
.image-usage-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border: 1px solid var(--color-gray-200, #e5e7eb);
    border-radius: 6px;
    overflow: hidden;
}

.image-usage-list-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--color-white, #fff);
    border-bottom: 1px solid var(--color-gray-100, #f3f4f6);
}

.image-usage-list-item:last-child {
    border-bottom: none;
}

.image-usage-list-item:hover {
    background: var(--color-gray-50, #f9fafb);
}

.image-usage-list-item-type {
    flex-shrink: 0;
    padding: 2px 8px;
    border-radius: 10px;
    background: var(--color-gray-100, #f3f4f6);
    color: var(--color-gray-600, #4b5563);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.image-usage-list-item-link {
    font-size: 13px;
    color: var(--color-primary, #2271b1);
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.image-usage-list-item-link:hover {
    text-decoration: underline;
}

.image-usage-list-empty {
    padding: 12px 14px;
    color: var(--color-gray-500, #6b7280);
    font-size: 13px;
}
