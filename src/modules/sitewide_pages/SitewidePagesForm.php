<?php

namespace Pageflow\Core\modules\sitewide_pages;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\modules\pages\service\PageInteractor;
use Pageflow\Core\modules\pages\service\PageService;

class SitewidePagesForm extends Form {

    private ?int $sitewidePageToAdd;
    private array $orderedPageIds = array();
    private array $sitewidePagesToDelete = array();
    private PageService $pageService;

    public function __construct() {
        $this->pageService = PageInteractor::getInstance();
    }

    public function loadFields(): void {
        $this->sitewidePageToAdd = $this->getNumber("add_sitewide_page_ref");
        $this->loadOrderedPageIds();
        $this->loadSitewidePagesToDelete();
    }

    public function getSitewidePageToAdd(): int {
        return $this->sitewidePageToAdd;
    }

    public function getSitewidePagesToDelete(): array {
        return $this->sitewidePagesToDelete;
    }

    public function getOrderedPageIds(): array {
        return $this->orderedPageIds;
    }

    private function loadOrderedPageIds(): void {
        $value = $_POST["sitewide_pages_order"] ?? "";
        if ($value != "") {
            $this->orderedPageIds = array_map('intval', explode(',', $value));
        }
    }

    private function loadSitewidePagesToDelete(): void {
        $sitewidePages = $this->pageService->getSitewidePages();
        foreach ($sitewidePages as $sitewidePage) {
            $fieldToCheck = "sitewide_page_" . $sitewidePage->getId() . "_delete";
            if (isset($_POST[$fieldToCheck]) && $_POST[$fieldToCheck] != "") {
                $this->sitewidePagesToDelete[] = $sitewidePage;
            }
        }
    }

}
    