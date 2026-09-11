<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\model\ArticleMetadataField;
use Pageflow\Core\modules\articles\model\ArticleMetadataFieldValue;
use Pageflow\Core\modules\articles\model\ArticleTerm;
use Pageflow\Core\modules\pages\model\Page;

interface ArticleDao {
    public function getArticle(int $id): ?Article;

    public function getArticleByElementHolderId(int $elementHolderId): ?Article;

    public function getAllArticles(): array;

    public function getAllChildArticles(int $parentArticleId): array;

    public function searchArticles(string $keyword, ?int $termId = null): array;

    public function getRandomArticles(?int $exclude, bool $published, int $numberOfResults, ?array $terms): array;

    public function getArticlesWhereImageIsUsedAsWallpaperOrLeadImage(int $imageId): array;

    public function advancedSearchArticles(?string $fromDate, ?string $toDate, ?string $orderBy, ?string $orderType, ?array $terms, ?int $maxResults, ?int $siblingsOnlyId, bool $published, ?int $exclude): array;

    public function updateArticle(Article $article): void;

    public function deleteArticle($article): void;

    public function createArticle(Article $article): void;

    public function getArticleComments(int $articleId): array;

    public function getChildArticleComments(int $commentId): array;

    public function getAllTerms(): array;

    public function getTerm($id): ?ArticleTerm;

    public function createTerm($termName): ArticleTerm;

    public function getTermByName($name): ?ArticleTerm;

    public function updateTerm($term): void;

    public function deleteTerm($term): void;

    public function getTermsForArticle(int $articleId): array;

    public function addTermToArticle($termId, $article): void;

    public function deleteTermFromArticle($termId, $article): void;

    public function addTargetPage($targetPageId): void;

    public function getTargetPages(): array;

    public function deleteTargetPage($targetPageId): void;

    public function getDefaultTargetPage(): ?Page;

    public function setDefaultArticleTargetPage($targetPageId): void;

    public function getMetadataFields(): array;

    public function getMetadataField(int $id): ?ArticleMetadataField;

    public function createNewArticleMetadataField(string $name): ArticleMetadataField;

    public function updateMetadataField(ArticleMetadataField $field): void;

    public function deleteMetadataField(ArticleMetadataField $field): void;

    public function getMetadataFieldValue(Article $article, ArticleMetadataField $field): ?ArticleMetadataFieldValue;

    public function updateMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void;

    public function addMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void;
}