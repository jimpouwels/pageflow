<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\database\dao\ImageDao;
use Pageflow\Core\database\dao\ImageDaoMysql;
use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\database\dao\WebformDao;
use Pageflow\Core\database\dao\WebformDaoMysql;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\service\ArticleInteractor;
use Pageflow\Core\modules\articles\service\ArticleService;
use Pageflow\Core\modules\links\database\dao\ReusableLinkDaoMysql;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;

class ArticleVisual extends FrontendVisual {

    private WebformDao $webformDao;
    private ArticleService $articleService;
    private ImageDao $imageDao;

    public function __construct(Page $page, Article $article) {
        parent::__construct($page, $article);
        $this->webformDao = WebformDaoMysql::getInstance();
        $this->articleService = ArticleInteractor::getInstance();
        $this->imageDao = ImageDaoMysql::getInstance();
    }

    public function getTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    public function loadVisual(?array &$data): void {
        $data["id"] = $this->getArticle()->getId();
        $data["title"] = $this->getArticle()->getTitle();
        $data["seo_title"] = $this->getArticle()->getSeoTitle();
        $data["description"] = $this->getArticle()->getDescription();
        $data["publication_date"] = $this->getArticle()->getPublicationDate();
        $data["sort_date"] = explode(' ', $this->getArticle()->getSortDate())[0];
        $data["image"] = $this->getImageData($this->imageDao->getImage($this->getArticle()->getImageId()));
        $data["wallpaper"] = $this->getImageData($this->imageDao->getImage($this->getArticle()->getWallpaperId()));
        $data["terms"] = $this->getTermList();
        $this->renderElementHolderContent($this->getArticle(), $data);
        $data["comments"] = $this->renderArticleComments($this->getArticle());
        $data["comment_webform"] = $this->renderArticleCommentWebForm($this->getArticle());

        foreach ($this->articleService->getMetadataFields() as $metadataField) {
            $fieldValue = $this->articleService->getMetadataFieldValue($this->getArticle(), $metadataField);

            $fieldData = array();
            $fieldData['default_value'] = $metadataField->getDefaultValue();
            $fieldData['dedicated_value'] = $fieldValue?->getValue();
            $fieldData['link'] = $this->getLinkData($metadataField->getLinkId());
            $fieldData['value'] = $fieldData['link']['url'] ?? ($fieldValue?->getValue() ?: $metadataField->getDefaultValue());

            $data[$metadataField->getName()] = $fieldData;
        }

        $data["parent_article"] = "";
        if ($this->getArticle()->getParentArticleId()) {
            $parentArticleData = array();
            $parentArticle = $this->articleService->getArticle($this->getArticle()->getParentArticleId());
            $parentArticleData["id"] = $parentArticle->getId();
            $parentArticleData["title"] = $parentArticle->getTitle();
            $parentArticleData["seo_title"] = $parentArticle->getSeoTitle();
            $parentArticleData["description"] = $parentArticle->getDescription();
            $parentArticleData["url"] = $this->getLinkHelper()->createArticleUrl($parentArticle);

            foreach ($this->articleService->getMetadataFields() as $metadataField) {
                $fieldValue = $this->articleService->getMetadataFieldValue($parentArticle, $metadataField);

                $fieldData = array();
                $fieldData['default_value'] = $metadataField->getDefaultValue();
                $fieldData['dedicated_value'] = $fieldValue?->getValue();
                $fieldData['link'] = $this->getLinkData($metadataField->getLinkId());
                $fieldData['value'] = $fieldData['link']['url'] ?? ($fieldValue?->getValue() ?: $metadataField->getDefaultValue());

                $parentArticleData[$metadataField->getName()] = $fieldData;
            }
            $data["parent_article"] = $parentArticleData;
        }
    }

    public function getPresentable(): ?Presentable {
        return $this->getArticle();
    }

    private function getLinkData(?int $linkId): ?array {
        if (!$linkId) {
            return null;
        }
        $link = ReusableLinkDaoMysql::getInstance()->getLink($linkId);
        if (!$link) {
            return null;
        }
        return ['url' => $link->getUrl(), 'title' => $link->getTitle()];
    }

    private function getImageData($image): ?array {
        $imageData = null;
        if ($image) {
            $imageData = array();
            $imageData["title"] = $image->getTitle();
            $imageData["alt_text"] = $image->getAltText();
            $imageData["url"] = $this->getLinkHelper()->createImageUrl($image);
            $imageData["url_mobile"] = $this->getLinkHelper()->createMobileImageUrl($image);
            $imageData["location"] = $image->getLocation();
        }
        return $imageData;
    }

    private function renderArticleComments(Article $article): array {
        $commentsData = array();
        foreach ($this->articleService->getArticleComments($article->getId()) as $comment) {
            $commentData = array();
            $commentData['id'] = htmlspecialchars($comment->getId());
            $commentData['name'] = htmlspecialchars($comment->getName());
            $commentData['message'] = nl2br(htmlspecialchars($comment->getMessage()));
            $commentData['created_at'] = $comment->getCreatedAt();
            $childComments = array();
            foreach ($this->articleService->getChildArticleComments($comment->getId()) as $child) {
                $childCommentData = array();
                $childCommentData['id'] = htmlspecialchars($child->getId());
                $childCommentData['created_at'] = $child->getCreatedAt();
                $childCommentData['name'] = htmlspecialchars($child->getName());
                $childCommentData['message'] = nl2br(htmlspecialchars($child->getMessage()));
                $childComments[] = $childCommentData;
            }
            $commentData['children'] = $childComments;
            $commentsData[] = $commentData;
        }
        return $commentsData;
    }

    private function renderArticleCommentWebForm($article): string {
        if (!is_null($article->getCommentWebFormId())) {
            $commentWebform = $this->webformDao->getWebForm($article->getCommentWebFormId());
            $formVisual = new FormFrontendVisual($this->getPage(), $article, $commentWebform);
            return $formVisual->render();
        }
        return "";
    }

    private function getTermList(): array {
        $termList = array();
        foreach ($this->articleService->getTermsForArticle($this->getArticle()) as $term) {
            $termList[] = $term->getName();
        }
        return $termList;
    }
}