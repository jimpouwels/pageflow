<div>
    <form method="post" id="update_sitewide_pages_form" action="{$backend_base_url}">
        <input type="hidden" name="action" id="action" value="" />
        <input type="hidden" name="sitewide_pages_order" id="sitewide_pages_order" value="" />

        <a href="#" id="update_sitewide_pages" class="displaynone"></a>
        {if !is_null($sitewide_pages) && count($sitewide_pages) > 0}
            <table class="listing">
                <colgroup style="width: 20px"></colgroup>
                <colgroup style="width: 225px"></colgroup>
                <colgroup style="width: 20px"></colgroup>
                <thead>
                <tr class="header">
                    <th></th>
                    <th>Paginatitel</th>
                    <th class="center_column">Verwijder</th>
                </tr>
                </thead>
                <tbody id="sitewide_pages_sortable_items" class="sitewide_pages_sortable_items">
                {foreach from=$sitewide_pages item=sitewide_page}
                    <tr class="sitewide_page_sortable_row" data-sitewide-page-id="{$sitewide_page.id}">
                        <td class="sitewide_page_drag_handle" title="Reorder" aria-label="Reorder">
                            <span></span>
                            <span></span>
                            <span></span>
                        </td>
                        <td>{$sitewide_page.title}</td>
                        <td class="delete_column center_column">
                            <label for="sitewide_page_{$sitewide_page.id}_delete" class="admin_label"></label>
                            <input type="checkbox" id="sitewide_page_{$sitewide_page.id}_delete"
                                   name="sitewide_page_{$sitewide_page.id}_delete" class="admin_field_checkbox" />
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}
        {$page_lookup}
    </form>
</div>