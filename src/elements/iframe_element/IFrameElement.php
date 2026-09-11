<?php

namespace Pageflow\Core\elements\iframe_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\iframe_element\dao\IFrameElementMetadataDao;
use Pageflow\Core\elements\iframe_element\visuals\IFrameElementEditor;
use Pageflow\Core\elements\iframe_element\visuals\IFrameElementStatics;
use Pageflow\Core\frontend\IFrameElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class IFrameElement extends Element {

    private ?string $url = null;
    private ?int $width = null;
    private ?int $height = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new IFrameElementMetadataDao($this));
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

    public function getStatics(): Visual {
        return new IFrameElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new IFrameElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): IFrameElementFrontendVisual {
        return new IFrameElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new IFrameElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        return "IFrame";
    }
}

