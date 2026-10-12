<?php

namespace Pageflow\Core\elements\html_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\html_element\dao\HtmlElementMetadataDao;
use Pageflow\Core\elements\html_element\visuals\HtmlElementEditor;
use Pageflow\Core\elements\html_element\visuals\HtmlElementStatics;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\frontend\HtmlElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class HtmlElement extends Element {

    private ?string $markup = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new HtmlElementMetadataDao($this));
    }

    public function setMarkup(?string $markup): void {
        $this->markup = $markup;
    }

    public function getMarkup(): ?string {
        return $this->markup;
    }

    public function getStatics(): Visual {
        return new HtmlElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new HtmlElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new HtmlElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new HtmlElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return "HTML";
    }

}
