<?php

namespace Pageflow\Core\elements\photo_album_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ImageDaoMysql;
use Pageflow\Core\elements\photo_album_element\dao\PhotoAlbumElementMetadataDao;
use Pageflow\Core\elements\photo_album_element\visuals\PhotoAlbumElementEditor;
use Pageflow\Core\elements\photo_album_element\visuals\PhotoAlbumElementStatics;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\frontend\PhotoAlbumElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class PhotoAlbumElement extends Element {
    private array $imageIds = array();
    private ?int $numberOfResults = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new PhotoAlbumElementMetadataDao($this));
    }

    public function setNumberOfResults(?int $numberOfResults): void {
        $this->numberOfResults = $numberOfResults;
    }

    public function getNumberOfResults(): ?int {
        return $this->numberOfResults;
    }

    public function addImage(int $imageId): void {
        $this->imageIds[] = $imageId;
    }

    public function deleteImage(int $imageIdToDelete): void {
        $this->imageIds = array_filter($this->imageIds, fn($imageId) => $imageId != $imageIdToDelete);
    }

    public function getImageIds(): array {
        return $this->imageIds;
    }

    public function setImageIds(array $imageIds): void {
        $this->imageIds = $imageIds;
    }

    public function getImages(): array {
        $results = array();
        $imageDao = ImageDaoMysql::getInstance();
        if (count($this->imageIds) > 0) {
            foreach ($this->imageIds as $imageId) {
                $image = $imageDao->getImage($imageId);
                if ($image) {
                    $results[] = $image;
                }
            }
        }
        if ($this->getNumberOfResults()) {
            $results = array_slice($results, 0, $this->getNumberOfResults());
        }
        return $results;
    }

    public function getStatics(): Visual {
        return new PhotoAlbumElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new PhotoAlbumElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new PhotoAlbumElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new PhotoAlbumElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        $summaryText = $this->getTitle() || '';
        $imageCount = count($this->getImageIds());
        if ($imageCount > 0) {
            $summaryText .= " ($imageCount images)";
        }
        return $summaryText;
    }
}

