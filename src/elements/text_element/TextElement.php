<?php

namespace Pageflow\Core\elements\text_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\text_element\dao\TextElementMetadataDao;
use Pageflow\Core\elements\text_element\visuals\TextElementEditor;
use Pageflow\Core\elements\text_element\visuals\TextElementStatics;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\frontend\TextElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class TextElement extends Element {

    private ?string $text = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new TextElementMetadataDao($this));
    }

    public function setText(?string $text): void {
        $this->text = $text;
    }

    public function getText(): ?string {
        return $this->text;
    }

    public function getStatics(): Visual {
        return new TextElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new TextElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new TextElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new TextElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        $summaryText = $this->getTitle();
        $summaryText .= ' (\'' . substr($this->getText() ?: "", 0, 50) . '...\')';
        return $summaryText;
    }

}

