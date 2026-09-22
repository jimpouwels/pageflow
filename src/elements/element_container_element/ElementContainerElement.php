<?php

namespace Pageflow\Core\elements\element_container_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\element_container_element\dao\ElementContainerElementMetadataDao;
use Pageflow\Core\elements\element_container_element\visuals\ElementContainerElementEditor;
use Pageflow\Core\elements\element_container_element\visuals\ElementContainerElementStatics;
use Pageflow\Core\frontend\ElementContainerElementFrontendVisual;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class ElementContainerElement extends Element {

    private ElementContainerElementMetadataDao $containerMetadataDao;

    public function __construct(int $scopeId) {
        $this->containerMetadataDao = new ElementContainerElementMetadataDao($this);
        parent::__construct($scopeId, $this->containerMetadataDao);
    }

    public function getStatics(): Visual {
        return new ElementContainerElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new ElementContainerElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new ElementContainerElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new ElementContainerElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return $this->getTitle() ?? "";
    }

    public function getChildElements(): array {
        return $this->containerMetadataDao->getChildElements($this->getId());
    }

    public function detachChildElements(): void {
        $this->containerMetadataDao->detachChildElements($this->getId());
    }

}
