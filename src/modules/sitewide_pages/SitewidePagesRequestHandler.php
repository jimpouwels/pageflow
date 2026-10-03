<?php

namespace Pageflow\Core\modules\sitewide_pages;

use Pageflow\Core\modules\pages\service\PageInteractor;
use Pageflow\Core\modules\pages\service\PageService;
use Pageflow\Core\request_handlers\HttpRequestHandler;

class SitewidePagesRequestHandler extends HttpRequestHandler {

    private PageService $pageService;
    private SitewidePagesForm $sitewidePagesForm;

    public function __construct() {
        $this->pageService = PageInteractor::getInstance();
        $this->sitewidePagesForm = new SitewidePagesForm();
    }

    public function handleGet(): void {
    }

    public function handlePost(): void {
        $this->sitewidePagesForm->loadFields();
        if ($this->getAction() == "add_sitewide_page") {
            $this->pageService->addSitewidePage($this->sitewidePagesForm->getSitewidePageToAdd());
        }
        if ($this->getAction() == "remove_sitewide_pages") {
            $this->deleteSitewidePages();
        }
        if ($this->getAction() == "reorder_sitewide_pages") {
            $this->reorderSitewidePages($this->sitewidePagesForm->getOrderedPageIds());
        }
    }

    private function deleteSitewidePages(): void {
        foreach ($this->sitewidePagesForm->getSitewidePagesToDelete() as $sitewidePageToDelete) {
            $this->pageService->removeSitewidePage($sitewidePageToDelete);
        }
    }

    private function getAction(): string {
        return $_POST["action"] ?? "";
    }

    private function reorderSitewidePages(array $orderedPageIds): void {
        $pagesById = array();
        foreach ($this->pageService->getSitewidePages() as $page) {
            $pagesById[$page->getId()] = $page;
        }
        $pages = array();
        foreach ($orderedPageIds as $pageId) {
            if (isset($pagesById[$pageId])) {
                $pages[] = $pagesById[$pageId];
            }
        }
        $this->pageService->updateSitewidePages($pages);
    }
}