<?php

namespace Pageflow\Core\elements\image_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ImageDaoMysql;
use Pageflow\Core\elements\image_element\dao\ImageElementMetadataDao;
use Pageflow\Core\elements\image_element\visuals\ImageElementEditor;
use Pageflow\Core\elements\image_element\visuals\ImageElementStatics;
use Pageflow\Core\frontend\ImageElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\images\model\Image;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class ImageElement extends Element {

    private ?string $align = null;
    private ?int $height = null;
    private ?int $width = null;
    private ?string $url = null;
    private ?int $imageId = null;
    private ?string $link = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new ImageElementMetadataDao($this));
    }

    public function setAlign(?string $align): void {
        $this->align = $align;
    }

    public function getAlign(): ?string {
        return $this->align;
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

    public function setImageId(?int $image_id): void {
        $this->imageId = $image_id;
    }

    public function getImageId(): ?int {
        return $this->imageId;
    }

    public function getImage(): ?Image {
        if ($this->imageId != null) {
            $image_dao = ImageDaoMysql::getInstance();
            return $image_dao->getImage($this->imageId);
        }
        return null;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setUrl(?string $url): void {
        $this->url = $url;
    }

    public function getLink(): ?string {
        return $this->link;
    }

    public function setLink(?string $link): void {
        $this->link = $link;
    }

    public function getStatics(): Visual {
        return new ImageElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new ImageElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): ImageElementFrontendVisual {
        return new ImageElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new ImageElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        $summaryText = $this->getTitle() ?: '';
        $image = $this->getImage();
        if ($image) {
            $summaryText .= ': ' . $image->getTitle() . ' (' . $image->getFilename() . ')';
        }
        return $summaryText;
    }
}
