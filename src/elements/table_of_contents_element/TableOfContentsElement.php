<?php

namespace Pageflow\Core\elements\table_of_contents_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\elements\table_of_contents_element\dao\TableOfContentsElementMetadataDao;
use Pageflow\Core\elements\table_of_contents_element\visuals\TableOfContentsElementEditor;
use Pageflow\Core\elements\table_of_contents_element\visuals\TableOfContentsElementStatics;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\frontend\TableOfContentsElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\TemplateEngine;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class TableOfContentsElement extends Element {

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new TableOfContentsElementMetadataDao($this));
    }

    public function getStatics(): Visual {
        return new TableOfContentsElementStatics(TemplateEngine::getInstance());
    }

    public function getBackendVisual(): ElementVisual {
        return new TableOfContentsElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new TableOfContentsElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new TableOfContentsElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return $this->getTitle() ?? "";
    }

}

