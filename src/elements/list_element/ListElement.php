<?php

namespace Pageflow\Core\elements\list_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\list_element\dao\ListElementMetaDataDao;
use Pageflow\Core\elements\list_element\visuals\ListElementEditor;
use Pageflow\Core\elements\list_element\visuals\ListElementStatics;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\frontend\ListElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class ListElement extends Element {

    private array $listItems = array();

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new ListElementMetaDataDao($this));
    }

    public function getListItems(): array {
        return $this->listItems;
    }

    public function setListItems(array $listItems): void {
        $this->listItems = $listItems;
    }

    public function addListItem(): void {
        $listItem = new ListItem();
        $listItem->setIndent(0);
        $listItem->setOrderNr(count($this->listItems));
        $this->listItems[] = $listItem;
    }

    public function deleteListItem(ListItem $listItemToDelete): void {
        $this->listItems = array_filter($this->listItems, function ($listItem) use ($listItemToDelete) {
            return $listItem->getId() !== $listItemToDelete->getId();
        });
        $this->getMetaDataDao()->deleteListItem($listItemToDelete);
    }

    public function getStatics(): Visual {
        return new ListElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new ListElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new ListElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new ListElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return $this->getTitle() || '';
    }
}

