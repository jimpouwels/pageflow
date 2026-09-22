{* Element selector modal - always available *}
<div id="element-selector-modal" class="element-selector-modal" style="display: none;">
    <div class="element-selector-backdrop" onclick="hideElementSelector();"></div>
    <div class="element-selector-content">
        <div class="element-selector-header">
            <h3>Element invoegen</h3>
            <button type="button" class="close-btn" onclick="hideElementSelector();">&times;</button>
        </div>
        <div class="element-selector-grid">
            {foreach from=$element_types item=type}
                <button type="button" class="element-type-btn" onclick="insertElementAtPosition('{$type.id}'); return false;">
                    <img src="{$type.icon_url}" alt="{$type.name}" />
                    <span>{$type.name}</span>
                </button>
            {/foreach}
        </div>
    </div>
</div>

{if isset($elements)}
    <div id="element_container" class="draggable_items" data-container-assignments="{$container_assignments_json|escape:'html'}">
        {* Insert button at the top *}
        <div class="element-insert-button" data-insert-position="0">
            <button type="button" class="insert-btn" onclick="showElementSelector(0); return false;" title="Element invoegen">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="insert-indicator">
                <div class="insert-line"></div>
                <div class="insert-arrow">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M6 2L6 10M6 10L3 7M6 10L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {foreach from=$elements item=element name=elementLoop}
            {$element}
            
            {* Insert button after each element *}
            <div class="element-insert-button" data-insert-position="{$smarty.foreach.elementLoop.iteration}">
                <button type="button" class="insert-btn" onclick="showElementSelector({$smarty.foreach.elementLoop.iteration}); return false;" title="Element invoegen">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="insert-indicator">
                    <div class="insert-line"></div>
                    <div class="insert-arrow">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M6 2L6 10M6 10L3 7M6 10L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{else}
    <div id="element_container" class="draggable_items empty-container" data-container-assignments="{$container_assignments_json|escape:'html'}">
        {* Insert button for empty container *}
        <div class="element-insert-button" data-insert-position="0">
            <button type="button" class="insert-btn" onclick="showElementSelector(0); return false;" title="Element invoegen">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="insert-indicator">
                <div class="insert-line"></div>
                <div class="insert-arrow">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M6 2L6 10M6 10L3 7M6 10L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
{/if}
