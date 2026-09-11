<?php

use Pageflow\Core\database\dao\ArticleDao;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\model\ArticleMetadataField;
use Pageflow\Core\modules\articles\model\ArticleMetadataFieldValue;
use Pageflow\Core\modules\articles\model\ArticleTerm;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\utilities\Arrays;

require_once(CMS_ROOT . '/database/dao/ArticleDao.php');

class ArticleDaoMock implements ArticleDao {

    private array $articles = array();

    public function getArticle($id): ?Article {
        // TODO: Implement getArticle() method.
        return null;
    }

    public function getArticleByElementHolderId($elementHolderId): ?Article {
        foreach ($this->articles as $article) {
            if ($article->getId() === $elementHolderId) {
                return $article;
            }
        }
        return null;
    }

    public function getAllArticles(): array {
        // TODO: Implement getAllArticles() method.
        return [];
    }

    public function getAllChildArticles(int $parentArticleId): array {
        // TODO: Implement getAllChildArticles() method.
        return [];
    }

    public function getArticlesWhereImageIsUsedAsWallpaperOrLeadImage(int $imageId): array {
        // TODO: Implement getArticlesWhereImageIsUsedAsWallpaperOrLeadImage() method.
        return [];
    }

    public function searchArticles(string $keyword, ?int $termId): array {
        // TODO: Implement searchArticles() method.
        return [];
    }

    public function advancedSearchArticles(?string $fromDate, ?string $toDate, ?string $orderBy, ?string $orderType, ?array $terms, ?int $maxResults, ?int $siblingsOnlyId, bool $published, ?int $exclude): array {
        // TODO: Implement advancedSearchArticles() method.
        return [];
    }

    public function updateArticle(Article $article): void {
        // TODO: Implement updateArticle() method.
    }

    public function deleteArticle($article): void {
        // TODO: Implement deleteArticle() method.
    }

    public function createArticle(Article $article): void {
        // TODO: Implement createArticle() method.
    }

    public function getArticleComments(int $articleId): array {
        // TODO: Implement getArticleComments() method.
        return [];
    }

    public function getChildArticleComments(int $commentId): array {
        // TODO: Implement getChildArticleComments() method.
        return [];
    }

    public function getAllTerms(): array {
        // TODO: Implement getAllTerms() method.
        return [];
    }

    public function getTerm($id): ?ArticleTerm {
        // TODO: Implement getTerm() method.
        return null;
    }

    public function createTerm($termName): ArticleTerm {
        // TODO: Implement createTerm() method.
        return new ArticleTerm();
    }

    public function getTermByName($name): ?ArticleTerm {
        // TODO: Implement getTermByName() method.
        return null;
    }

    public function updateTerm($term): void {
        // TODO: Implement updateTerm() method.
    }

    public function deleteTerm($term): void {
        // TODO: Implement deleteTerm() method.
    }

    public function getTermsForArticle(int $articleId): array {
        // TODO: Implement getTermsForArticle() method.
        return [];
    }

    public function addTermToArticle($termId, $article): void {
        // TODO: Implement addTermToArticle() method.
    }

    public function deleteTermFromArticle($termId, $article): void {
        // TODO: Implement deleteTermFromArticle() method.
    }

    public function addTargetPage($targetPageId): void {
        // TODO: Implement addTargetPage() method.
    }

    public function getTargetPages(): array {
        // TODO: Implement getTargetPages() method.
        return [];
    }

    public function deleteTargetPage($targetPageId): void {
        // TODO: Implement deleteTargetPage() method.
    }

    public function getDefaultTargetPage(): ?Page {
        // TODO: Implement getDefaultTargetPage() method.
        return null;
    }

    public function setDefaultArticleTargetPage($targetPageId): void {
        // TODO: Implement setDefaultArticleTargetPage() method.
    }

    public function addArticle(Article $article): void {
        $this->articles[] = $article;
    }

    public function getMetadataFields(): array {
        // TODO: Implement getMetadataFields() method.
        return [];
    }

    public function getMetadataField(int $id): ?ArticleMetadataField {
        // TODO: Implement getMetadataField() method.
        return null;
    }

    public function createNewArticleMetadataField(string $name): ArticleMetadataField {
        // TODO: Implement createNewArticleMetadataField() method.
        return new ArticleMetadataField();
    }

    public function updateMetadataField(ArticleMetadataField $field): void {
        // TODO: Implement updateMetadataField() method.
    }

    public function deleteMetadataField(ArticleMetadataField $field): void {
        // TODO: Implement deleteMetadataField() method.
    }

    public function getMetadataFieldValue(Article $article, ArticleMetadataField $field): ?ArticleMetadataFieldValue {
        // TODO: Implement getMetadataFieldValue() method.
        return null;
    }

    public function updateMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void {
        // TODO: Implement updateMetadataFieldValue() method.
    }

    public function addMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void {
        // TODO: Implement addMetadataFieldValue() method.
    }
}