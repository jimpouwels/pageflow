<?php

namespace Pageflow\Core\modules\articles\service;

use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\model\ArticleMetadataField;
use Pageflow\Core\modules\articles\model\ArticleMetadataFieldValue;
use Pageflow\Core\modules\articles\model\ArticleTerm;
use Pageflow\Core\modules\pages\model\Page;

interface ArticleService {
    public function updateArticle(Article $article): void;

    public function getAllChildArticlesFor(Article $article): array;

    public function createArticle(): Article;

    public function deleteArticle(Article $article): void;

    public function getTermsForArticle(Article $article): array;

    public function addTermToArticle(int $termId, Article $article): void;

    public function deleteTermFromArticle(int $termId, Article $article): void;

    public function getArticle(int $id): Article;

    public function getArticleComments(int $articleId): array;

    public function getChildArticleComments(int $commentId): array;

    public function deleteTargetPage(Page $page): void;

    public function setDefaultArticleTargetPage(int $pageId): void;

    public function addTargetPage(int $pageId): void;

    public function searchArticles(?string $searchQuery, ?int $termId): array;

    public function getArticlesWhereImageIsUsedAsWallpaperOrLeadImage(int $imageId): array;

    public function getAllArticles(): array;

    public function getTerm(string $name): ArticleTerm;

    public function getMetadataFields(): array;

    public function getMetadataField(int $id): ?ArticleMetadataField;

    public function createNewArticleMetadataField(string $name): ArticleMetadataField;

    public function updateMetadataField(ArticleMetadataField $field): void;

    public function deleteMetadataField(ArticleMetadataField $field): void;

    public function getMetadataFieldValue(Article $article, ArticleMetadataField $field): ?ArticleMetadataFieldValue;

    public function updateMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void;

    public function addMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void;
}