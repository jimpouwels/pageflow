<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\elements\element_container_element\ElementContainerElement;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;

class ElementContainerElementFrontendVisual extends ElementFrontendVisual {

    public function __construct(Page $page, ?Article $article, ?Block $block, ElementContainerElement $element) {
        parent::__construct($page, $article, $block, $element);
    }

    public function loadElement(array &$data): void {
        $data["title"] = $this->getElement()->getTitle();
        $data["children"] = $this->renderChildren();
    }

    private function renderChildren(): array {
        $rendered = array();
        foreach ($this->getElement()->getChildElements() as $childElement) {
            if ($childElement->getTemplate()) {
                $childVisual = $childElement->getFrontendVisual($this->getPage(), $this->getArticle(), $this->getBlock());
                $rendered[] = $childVisual->render();
            }
        }
        return $rendered;
    }

    protected function getElement(): ElementContainerElement {
        return parent::getElement();
    }

}
