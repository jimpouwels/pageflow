<?php

namespace Pageflow\Core\database\dao;


use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\model\ArticleComment;
use Pageflow\Core\modules\articles\model\ArticleMetadataField;
use Pageflow\Core\modules\articles\model\ArticleMetadataFieldValue;
use Pageflow\Core\modules\articles\model\ArticleTerm;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\utilities\DateUtility;

class ArticleDaoMysql implements ArticleDao {
    private static string $myAllColumns = "e.id, e.template_id, e.name, e.title, e.published, e.last_modified, e.scope_id,
                      e.created_at, e.created_by, e.type, e.version, a.description, a.seo_title, a.wallpaper_id, a.url_title, a.image_id, a.template_id, a.parent_article_id, a.publication_date, a.sort_date, a.target_page, a.comment_webform_id";

    private static ?ArticleDaoMysql $instance = null;
    private PageDao $pageDao;
    private ElementHolderDao $elementHolderDao;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->pageDao = PageDaoMysql::getInstance();
        $this->elementHolderDao = ElementHolderDaoMysql::getInstance();
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): ArticleDaoMysql {
        if (!self::$instance) {
            self::$instance = new ArticleDaoMysql();
        }
        return self::$instance;
    }

    public function getArticle(int $id): ?Article {
        $statement = $this->mysqlConnector->prepareStatement("SELECT " . self::$myAllColumns . " FROM
                                                                    element_holders e, articles a WHERE e.id = ?
                                                                    AND e.id = a.element_holder_id");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return Article::constructFromRecord($row);
        }
        return null;
    }

    public function getArticleByElementHolderId(int $elementHolderId): ?Article {
        $statement = $this->mysqlConnector->prepareStatement("SELECT " . self::$myAllColumns . " FROM
                                                                    element_holders e, articles a WHERE a.element_holder_id = ?
                                                                    AND e.id = a.element_holder_id");
        $statement->bind_param("i", $elementHolderId);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return Article::constructFromRecord($row);
        }
        return null;
    }

    public function getAllArticles(): array {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, articles a WHERE e.id = a.element_holder_id
                      order by name ASC";
        $result = $this->mysqlConnector->executeQuery($query);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function getAllChildArticles(int $parentArticleId): array {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, articles a WHERE e.id = a.element_holder_id
                      AND parent_article_id = " . $parentArticleId . " order by created_at DESC";
        $result = $this->mysqlConnector->executeQuery($query);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function getArticlesWhereImageIsUsedAsWallpaperOrLeadImage(int $imageId): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT " . self::$myAllColumns . " FROM
                                                                    element_holders e, articles a WHERE e.id = a.element_holder_id
                                                                    AND (a.image_id = ? OR a.wallpaper_id = ?)");
        $statement->bind_param("ii", $imageId, $imageId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function searchArticles(string $keyword, ?int $termId = null): array {
        $from = " FROM element_holders e, articles a";
        $where = " WHERE
                      e.id = a.element_holder_id";
        if ($keyword)
            $where = $where . " AND e.name LIKE '%" . $keyword . "%'";
        if ($termId) {
            $from = $from . ", articles_terms ats";
            $where = $where . " AND ats.term_id = " . $termId . " AND ats.article_id = e.id";
        }

        $query = "SELECT DISTINCT " . self::$myAllColumns . $from . $where . " ORDER BY name";
        $result = $this->mysqlConnector->executeQuery($query);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function getRandomArticles(?int $exclude, bool $published, int $numberOfResults, ?array $terms): array {
        $queryFrom = "element_holders e, articles a";
        $queryWhere = " WHERE e.id = a.element_holder_id";
        if ($published) {
            $queryWhere = $queryWhere . " AND published = 1";
        }
        if ($exclude) {
            $queryWhere .= " AND e.id != " . $exclude;
        }
        if ($terms && count($terms) > 0) {
            $queryFrom = $queryFrom . ", articles_terms at";
            $termWhere = "";
            foreach ($terms as $term) {
                if ($termWhere) {
                    $termWhere .= ' OR ';
                } else {
                    $termWhere .= ' AND (';
                }
                $termWhere = $termWhere . "EXISTS(SELECT * FROM articles_terms at WHERE at.article_id = e.id AND at.term_id = " . $term->getId() . ")";
            }
            $queryWhere .= $termWhere . ')';
        }
        $query = "SELECT DISTINCT " . self::$myAllColumns . " FROM " . $queryFrom . " " . $queryWhere;
        $query .= " ORDER BY RAND() LIMIT " . $numberOfResults;
        $result = $this->mysqlConnector->executeQuery($query);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function advancedSearchArticles(?string $fromDate, ?string $toDate, ?string $orderBy, ?string $orderType, ?array $terms, ?int $maxResults, ?int $siblingsOnlyId, bool $published, ?int $exclude = null): array {
        $queryWhere = " WHERE e.id = a.element_holder_id";
        if ($published) {
            $queryWhere = $queryWhere . " AND published = 1";
        }
        $queryWhere = $queryWhere . " AND publication_date <= now()";
        if ($toDate) {
            $queryWhere = $queryWhere . " AND publication_date <= '" . DateUtility::stringMySqlDate($toDate) . "'";
        }
        if ($fromDate) {
            $queryWhere = $queryWhere . " AND publication_date > '" . DateUtility::stringMySqlDate($fromDate) . "'";
        }
        if ($exclude) {
            $queryWhere .= " AND e.id != " . $exclude;
        }
        if ($siblingsOnlyId) {
            $queryWhere .= " AND a.parent_article_id = " . $siblingsOnlyId;
        }
        $queryFrom = " FROM element_holders e, articles a";
        if ($terms && count($terms) > 0) {
            $queryFrom = $queryFrom . ", articles_terms at";
            $termWhere = "";
            foreach ($terms as $term) {
                if ($termWhere) {
                    $termWhere .= ' OR ';
                } else {
                    $termWhere .= ' AND (';
                }
                $termWhere = $termWhere . "EXISTS(SELECT * FROM articles_terms at WHERE at.article_id = e.id AND at.term_id = " . $term->getId() . ")";
            }
            $queryWhere .= $termWhere . ')';
        }
        $limitQueryPart = '';
        if ($maxResults) {
            $limitQueryPart = " LIMIT " . $maxResults;
        }

        $orderQueryPart = '';
        if ($orderBy) {
            switch ($orderBy) {
                case "Alphabet":
                    $orderQueryPart = 'e.title';
                    break;
                case "PublicationDate":
                    $orderQueryPart = 'a.publication_date ' . $orderType;
                    break;
                case "SortDate":
                    $orderQueryPart = 'a.sort_date ' . $orderType;
                    break;
            }
        }
        $query = "SELECT DISTINCT " . self::$myAllColumns . $queryFrom . $queryWhere . " ORDER BY " . $orderQueryPart . $limitQueryPart;
        $result = $this->mysqlConnector->executeQuery($query);
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = Article::constructFromRecord($row);
        }
        return $articles;
    }

    public function updateArticle(Article $article): void {
        $description = $article->getDescription();
        $seoTitle = $article->getSeoTitle();
        $publicationDate = $article->getPublicationDate();
        $sortDate = $article->getSortDate();
        $imageId = $article->getImageId();
        $wallpaperId = $article->getWallpaperId();
        $targetPage = $article->getTargetPageId();
        $parentArticleId = $article->getParentArticleId();
        $templateId = $article->getTemplateId();
        $urlTitle = $article->getUrlTitle();
        $commentWebformId = $article->getCommentWebFormId();
        $elementHolderId = $article->getId();

        $statement = $this->mysqlConnector->prepareStatement("UPDATE articles SET description = ?, seo_title = ?, publication_date = ?, url_title = ?, sort_date = ?, image_id = ?, wallpaper_id = ?, target_page = ?, parent_article_id = ?, template_id = ?, comment_webform_id = ? WHERE element_holder_id = ?");
        $statement->bind_param('ssssssiiiiii', $description, $seoTitle, $publicationDate, $urlTitle, $sortDate, $imageId, $wallpaperId, $targetPage, $parentArticleId, $templateId, $commentWebformId, $elementHolderId);

        $this->mysqlConnector->executeStatement($statement);
        $this->elementHolderDao->update($article);
    }

    public function deleteArticle($article): void {
        $this->elementHolderDao->delete($article);
    }

    public function createArticle(Article $article): void {
        $this->elementHolderDao->persist($article);
        $query = "INSERT INTO articles (description, image_id, element_holder_id, sort_date, publication_date, target_page) VALUES
                      (NULL, NULL, " . $article->getId() . ", now(), now(), NULL)";
        $this->mysqlConnector->executeQuery($query);
    }

    public function getArticleComments(int $articleId): array {
        $query = "SELECT * FROM article_comments WHERE article_id = ? AND parent IS NULL";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $articleId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $comments = array();
        while ($row = $result->fetch_assoc()) {
            $comments[] = ArticleComment::constructFromRecord($row);
        }
        return $comments;
    }

    public function getChildArticleComments(int $commentId): array {
        $query = "SELECT * FROM article_comments WHERE parent = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $commentId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $comments = array();
        while ($row = $result->fetch_assoc()) {
            $comments[] = ArticleComment::constructFromRecord($row);
        }
        return $comments;
    }

    public function getAllTerms(): array {
        $query = "SELECT * FROM article_terms ORDER by name ASC";
        $terms = array();
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            $terms[] = ArticleTerm::constructFromRecord($row);
        }
        return $terms;
    }

    public function getTerm($id): ?ArticleTerm {
        $query = "SELECT * FROM article_terms WHERE id = " . $id;
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return ArticleTerm::constructFromRecord($row);
        }
        return null;
    }

    public function createTerm($termName): ArticleTerm {
        $newTerm = new ArticleTerm();
        $newTerm->setName($termName);
        $postfix = 1;
        while (!is_null($this->getTermByName($newTerm->getName()))) {
            $newTerm->setName($termName . " " . $postfix);
            $postfix++;
        }
        $this->persistTerm($newTerm);
        return $newTerm;
    }

    public function getTermByName($name): ?ArticleTerm {
        $query = "SELECT * FROM article_terms WHERE name = '" . $name . "'";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return ArticleTerm::constructFromRecord($row);
        }
        return null;
    }

    private function persistTerm($term): void {
        $query = "INSERT INTO article_terms (name) VALUES  ('" . $term->getName() . "')";
        $this->mysqlConnector->executeQuery($query);
        $term->setId($this->mysqlConnector->getInsertId());
    }

    public function updateTerm($term): void {
        $query = "UPDATE article_terms SET name = '" . $term->getName() .
            "' WHERE id = " . $term->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function deleteTerm($term): void {
        $query = "DELETE FROM article_terms WHERE id = " . $term->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function getTermsForArticle(int $articleId): array {
        $query = "SELECT at.id, at.name FROM article_terms at, articles_terms ats,
                      element_holders e WHERE ats.article_id = " . $articleId . " AND ats.article_id =
                      e.id AND at.id = ats.term_id";

        $result = $this->mysqlConnector->executeQuery($query);
        $terms = array();
        while ($row = $result->fetch_assoc()) {
            $terms[] = ArticleTerm::constructFromRecord($row);
        }
        return $terms;
    }

    public function addTermToArticle($termId, $article): void {
        $query = "INSERT INTO articles_terms (article_id, term_id) VALUES (" . $article->getId() . ", " . $termId . ")";
        $this->mysqlConnector->executeQuery($query);
    }

    public function deleteTermFromArticle($termId, $article): void {
        $query = "DELETE FROM articles_terms WHERE article_id = " . $article->getId() . "
                      AND term_id = " . $termId;
        $this->mysqlConnector->executeQuery($query);
    }

    public function addTargetPage($targetPageId): void {
        $statement = $this->mysqlConnector->prepareStatement("SELECT count(*) AS number_of FROM article_target_pages WHERE element_holder_id = ?");
        $statement->bind_param("i", $targetPageId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $count = $row['number_of'];
            break;
        }

        if ($count == 0) {
            $statement = $this->mysqlConnector->prepareStatement("INSERT INTO article_target_pages (element_holder_id, is_default) VALUES (?, 0)");
            $statement->bind_param("i", $targetPageId);
            $this->mysqlConnector->executeStatement($statement);

            // check if only one target page is present
            $this->updateDefaultArticleTargetPage();
        }
    }

    private function updateDefaultArticleTargetPage(): void {
        $target_pages = $this->getTargetPages();
        if (count($target_pages) == 1) {
            $query = "UPDATE article_target_pages SET is_default = 1";
            $this->mysqlConnector->executeQuery($query);
        }
    }

    public function getTargetPages(): array {
        $query = "SELECT element_holder_id FROM article_target_pages";
        $result = $this->mysqlConnector->executeQuery($query);
        $pages = array();
        while ($row = $result->fetch_assoc()) {
            $pages[] = $this->pageDao->getPage($row['element_holder_id']);
        }
        return $pages;
    }

    public function deleteTargetPage($targetPageId): void {
        $query = "DELETE FROM article_target_pages where element_holder_id = " . $targetPageId;
        $this->mysqlConnector->executeQuery($query);

        // check if only one target page is present
        $this->updateDefaultArticleTargetPage();
    }

    public function getDefaultTargetPage(): ?Page {
        $query = "SELECT element_holder_id FROM article_target_pages WHERE is_default = 1";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return $this->pageDao->getPage($row["element_holder_id"]);
        }
        return null;
    }

    public function setDefaultArticleTargetPage($targetPageId): void {
        $query1 = "UPDATE article_target_pages SET is_default = 0 WHERE is_default = 1";
        $query2 = "UPDATE article_target_pages SET is_default = 1 WHERE element_holder_id = " . $targetPageId;
        $this->mysqlConnector->executeQuery($query1);
        $this->mysqlConnector->executeQuery($query2);
    }

    public function getMetadataFields(): array {
        $query = "SELECT * FROM article_metadata_fields";
        $result = $this->mysqlConnector->executeQuery($query);
        $fields = array();
        while ($row = $result->fetch_assoc()) {
            $fields[] = ArticleMetadataField::constructFromRecord($row);
        }
        return $fields;
    }

    public function getMetadataField(int $id): ?ArticleMetadataField {
        $query = "SELECT * FROM article_metadata_fields WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return ArticleMetadataField::constructFromRecord($row);
        }
        return null;
    }

    public function createNewArticleMetadataField(string $name): ArticleMetadataField {
        $query = "INSERT INTO article_metadata_fields (name) VALUES (?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param("s", $name);
        $this->mysqlConnector->executeStatement($statement);
        $field = new ArticleMetadataField();
        $field->setName($name);
        $field->setId($this->mysqlConnector->getInsertId());
        return $field;
    }

    public function updateMetadataField(ArticleMetadataField $field): void {
        $query = "UPDATE article_metadata_fields SET name = ?, default_value = ?, link_id = ? WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $field->getId();
        $name = $field->getName();
        $defaultValue = $field->getDefaultValue();
        $linkId = $field->getLinkId();
        $statement->bind_param("ssii", $name, $defaultValue, $linkId, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteMetadataField(ArticleMetadataField $field): void {
        $query = "DELETE FROM article_metadata_fields WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $field->getId();
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getMetadataFieldValue(Article $article, ArticleMetadataField $field): ?ArticleMetadataFieldValue {
        $query = "SELECT * FROM articles_metadata_fields WHERE article_id = ? AND metadata_field_id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $articleId = $article->getId();
        $fieldId = $field->getId();
        $statement->bind_param("ii", $articleId, $fieldId);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return ArticleMetadataFieldValue::constructFromRecord($row);
        }
        return null;
    }

    public function updateMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void {
        $query = "UPDATE articles_metadata_fields SET value = ? WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $fieldValue->getId();
        $value = $fieldValue->getValue();
        $statement->bind_param("si", $value, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function addMetadataFieldValue(ArticleMetadataFieldValue $fieldValue): void {
        $query = "INSERT INTO articles_metadata_fields (article_id, metadata_field_id, value) VALUES (?, ?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $articleId = $fieldValue->getArticleId();
        $metadataFieldId = $fieldValue->getMetadataFieldId();
        $value = $fieldValue->getValue();
        $statement->bind_param("iis", $articleId, $metadataFieldId, $value);
        $this->mysqlConnector->executeStatement($statement);
    }

}
