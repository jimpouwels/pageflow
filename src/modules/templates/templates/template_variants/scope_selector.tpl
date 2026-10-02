<ul>
    <li class="{if $showing_unassigned}active{/if}">
        <a id="scope_item_link" href="{$backend_base_url}&unassigned=1">{$text_resources.templates_unassigned_scope_label}</a>
    </li>
    {foreach from=$scopes item=scope}
        {assign var=li_class value=""}
        {if $scope.is_active}
            {assign var=li_class value="active"}
        {/if}
        <li class="{$li_class}">
            <a id="scope_item_link" href="{$backend_base_url}&scope={$scope.identifier}">{$scope.label}</a>
        </li>
    {/foreach}
</ul>