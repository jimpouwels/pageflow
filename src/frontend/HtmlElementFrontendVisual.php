<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\elements\html_element\HtmlElement;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;

class HtmlElementFrontendVisual extends ElementFrontendVisual {

    public function __construct(Page $page, ?Article $article, ?Block $block, HtmlElement $element) {
        parent::__construct($page, $article, $block, $element);
    }

    public function loadElement(array &$data): void {
        $data["html"] = $this->getElement()->getMarkup();
    }
}
