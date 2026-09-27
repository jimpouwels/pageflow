<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\database\dao\FunctionalImageDao;
use Pageflow\Core\database\dao\FunctionalImageDaoMysql;
use Pageflow\Core\elements\list_element\ListElement;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;

class ListElementFrontendVisual extends ElementFrontendVisual {

    private FunctionalImageDao $functionalImageDao;

    public function __construct(Page $page, ?Article $article, ?Block $block, ListElement $listElement) {
        parent::__construct($page, $article, $block, $listElement);
        $this->functionalImageDao = FunctionalImageDaoMysql::getInstance();
    }

    public function loadElement(array &$data): void {
        $data["title"] = $this->toHtml($this->getElement()->getTitle());
        $data["items"] = $this->renderListItems();
    }

    private function renderListItems(): array {
        $listItems = array();
        
        foreach ($this->getElement()->getListItems() as     $listItem) {
            $item = ['text' => $this->toHtml($listItem->getText())];
            if ($listItem->getFunctionalImageId()) {
                $fimg = $this->functionalImageDao->getFunctionalImage($listItem->getFunctionalImageId());
                $item['functional_image_url'] = $this->getLinkHelper()->createFunctionalImageUrl($fimg);
            }
            $listItems[] = $item;
        }
        
        return $listItems;
    }
}