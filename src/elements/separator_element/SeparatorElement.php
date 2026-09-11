<?php

namespace Pageflow\Core\elements\separator_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\separator_element\dao\SeparatorElementMetadataDao;
use Pageflow\Core\elements\separator_element\visuals\SeparatorElementEditor;
use Pageflow\Core\elements\separator_element\visuals\SeparatorElementStatics;
use Pageflow\Core\frontend\SeparatorElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class SeparatorElement extends Element {

    private ?string $url = null;
    private ?int $width = null;
    private ?int $height = null;
    private ?string $htmlId = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new SeparatorElementMetadataDao($this));
    }

    public function setUrl(?string $url): void {
        $this->url = $url;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function getWidth(): ?int {
        return $this->width;
    }

    public function setWidth(?int $width): void {
        $this->width = $width;
    }

    public function getHeight(): ?int {
        return $this->height;
    }

    public function setHeight(?int $height): void {
        $this->height = $height;
    }

    public function getHtmlId(): ?string {
        return $this->htmlId;
    }

    public function setHtmlId(?string $htmlId): void {
        $this->htmlId = $htmlId;
    }

    public function getStatics(): Visual {
        return new SeparatorElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new SeparatorElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): SeparatorElementFrontendVisual {
        return new SeparatorElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new SeparatorElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return "Separator";
    }
}

