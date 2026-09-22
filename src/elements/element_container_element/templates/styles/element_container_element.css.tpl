.element_container_children_wrapper {
    margin-top: 10px;
    padding: 10px;
    border: 1px dashed #c0c0c0;
    border-radius: 4px;
    background: #fafafa;
}

.element_container_child_list {
    min-height: 20px;
    padding-left: 40px;
    position: relative;
}

.element_container_child_list.empty-container {
    min-height: 40px;
    padding-left: 0;
}

/* Match empty element-holder plus style inside container: centered circle + no bar */
.element_container_child_list.empty-container > .element-insert-button {
    justify-content: center;
    height: auto;
    margin: 12px 0;
}

.element_container_child_list.empty-container > .element-insert-button .insert-btn {
    position: static;
    left: auto;
    top: auto;
    transform: none;
    opacity: 1;
    display: inline-flex;
    width: 32px;
    height: 32px;
}

.element_container_child_list.empty-container > .element-insert-button:hover .insert-btn {
    transform: scale(1.1);
}

.element_container_child_list.empty-container > .element-insert-button .insert-indicator {
    display: none;
}
