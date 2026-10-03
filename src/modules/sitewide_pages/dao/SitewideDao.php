<?php

namespace Pageflow\Core\modules\sitewide_pages\dao;

interface SitewideDao {
    public function getSitewidePages(): array;

    public function addSitewidePage(int $id): void;

    public function removeSitewidePage(int $id): void;

    public function updateSitewidePage(int $id, int $orderNumber): void;
}