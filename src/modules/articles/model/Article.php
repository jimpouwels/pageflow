<?php

namespace Pageflow\Core\modules\articles\model;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\core\model\ElementHolderType;

class Article extends ElementHolder {

    private static int $SCOPE = 9;
    private ?string $description;
    private ?string $seoTitle = null;
    private ?int $imageId = null;
    private ?int $wallpaperId = null;
    private ?string $urlTitle = null;
    private string $publicationDate;
    private string $sortDate;
    private ?int $targetPageId = null;
    private ?int $parentArticleId = null;
    private ?int $commentWebformId = null;

    public function __construct() {
        parent::__construct(self::$SCOPE, ElementHolderType::ARTICLE);
        $this->setPublished(false);
    }

    public static function constructFromRecord(array $row): Article {
        $article = new Article();
        $article->initFromDb($row);
        return $article;
    }

    protected function initFromDb(array $row): void {
        $this->setDescription($row['description']);
        $this->setSeoTitle($row['seo_title']);
        $this->setImageId($row['image_id']);
        $this->setWallpaperId($row['wallpaper_id']);
        $this->setUrlTitle($row['url_title']);
        $this->setPublicationDate($row['publication_date']);
        $this->setSortDate($row['sort_date']);
        $this->setTargetPageId(!is_null($row['target_page']) ? intval($row['target_page']) : null);
        $this->setParentArticleId(!is_null($row['parent_article_id']) ? intval($row['parent_article_id']) : null);
        $this->setCommentWebFormId($row['comment_webform_id']);
        parent::initFromDb($row);
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getSeoTitle(): ?string {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): void {
        $this->seoTitle = $seoTitle;
    }

    public function setUrlTitle(?string $urlTitle): void {
        $this->urlTitle = $urlTitle;
    }

    public function getUrlTitle(): ?string {
        return $this->urlTitle;
    }

    public function getImageId(): ?int {
        return $this->imageId;
    }

    public function setImageId(?int $imageId): void {
        $this->imageId = $imageId;
    }

    public function getWallpaperId(): ?int {
        return $this->wallpaperId;
    }

    public function setWallpaperId(?int $wallpaperId): void {
        $this->wallpaperId = $wallpaperId;
    }

    public function getPublicationDate(): string {
        return $this->publicationDate;
    }

    public function setPublicationDate(string $publicationDate): void {
        $this->publicationDate = $publicationDate;
    }

    public function getSortDate(): string {
        return $this->sortDate;
    }

    public function setSortDate(string $sortDate): void {
        $this->sortDate = $sortDate;
    }

    public function getTargetPageId(): ?int {
        return $this->targetPageId;
    }

    public function setTargetPageId(?int $targetPageId): void {
        $this->targetPageId = $targetPageId;
    }

    public function getParentArticleId(): ?int {
        return $this->parentArticleId;
    }

    public function setParentArticleId(?int $parentArticleId): void {
        $this->parentArticleId = $parentArticleId;
    }

    public function getCommentWebFormId(): ?int {
        return $this->commentWebformId;
    }

    public function setCommentWebFormId(?int $comment_webform_id): void {
        $this->commentWebformId = $comment_webform_id;
    }

}