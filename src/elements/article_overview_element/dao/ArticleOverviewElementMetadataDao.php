<?php

namespace Pageflow\Core\elements\article_overview_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\dao\ArticleDao;
use Pageflow\Core\database\dao\ArticleDaoMysql;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\articles\model\ArticleTerm;

class ArticleOverviewElementMetadataDao extends ElementMetadataDao
{

    private ArticleDao $articleDao;
    private Element $_element;
    private MysqlConnector $mysqlConnector;

    public function __construct($element) {
        parent::__construct($element);
        $this->_element = $element;
        $this->articleDao = ArticleDaoMysql::getInstance();
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "article_overview_elements_metadata";
    }

    public function constructMetaData(array $record, $element): void {
        $element->setTitle($record['title']);
        $element->setShowFrom($record['show_from']);
        $element->setShowTo($record['show_to']);
        $element->setOrderBy($record['order_by']);
        $element->setOrderType($record['order_type']);
        $element->setNumberOfResults($record['number_of_results']);
        $element->setTerms($this->getTerms());
        $element->setSiblingsOnly($record['siblings_only']);
        $element->setIncludeCurrentArticle($record['include_current_article']);
        $element->setRandomArticles($record['choose_random_articles']);
    }

    public function update(Element $element): void {
        $query = "UPDATE article_overview_elements_metadata SET title = '" . $element->getTitle() . "', ";
        if ($element->getShowFrom() == '') {
            $query = $query . "show_from = NULL, ";
        } else {
            $query = $query . "show_from = '" . $element->getShowFrom() . "',";
        }
        if ($element->getShowTo() == '') {
            $query = $query . "show_to = NULL, ";
        } else {
            $query = $query . "show_to = '" . $element->getShowTo() . "',";
        }
        if ($element->getNumberOfResults() == '') {
            $query = $query . "number_of_results = NULL, ";
        } else {
            $query = $query . "number_of_results = " . $element->getNumberOfResults() . ",";
        }
        $query = $query . " order_by = '" . $element->getOrderBy() . "', order_type = '" . $element->getOrderType() . "', siblings_only = " . ($element->getSiblingsOnly() ? 1 : 0) .
            ", include_current_article = " . ($element->includeCurrentArticle() ?  1 : 0) . ", choose_random_articles = " . ($element->isRandomArticles() ? 1 : 0) . " WHERE element_id = " . $element->getId();

        $this->mysqlConnector->executeQuery($query);
        $this->addTerms();
    }

    public function insert(Element $element): void {
        $query = "INSERT INTO article_overview_elements_metadata (title, show_from, show_to, order_by, order_type, element_id, number_of_results, choose_random_articles) VALUES
                        ('" . $element->getTitle() . "', NULL, NULL, 'PublicationDate', 'asc', " . $element->getId() . ", NULL, " . ($element->isRandomArticles() ? 1 : 0) . ")";
        $this->mysqlConnector->executeQuery($query);
        $this->addTerms();
    }

    private function getTerms(): array {
        $query = "SELECT * FROM articles_element_terms WHERE element_id = " . $this->_element->getId();
        $result = $this->mysqlConnector->executeQuery($query);
        $terms = array();
        while ($row = $result->fetch_assoc()) {
            array_push($terms, $this->articleDao->getTerm($row['term_id']));
        }
        return $terms;
    }

    private function addTerms(): void {
        $existingTerms = $this->getTerms();
        foreach ($existingTerms as $existingTerm) {
            if (!in_array($existingTerm, $this->_element->getTerms())) {
                $this->removeTerm($existingTerm);
            }
        }
        foreach ($this->_element->getTerms() as $term) {
            if (!in_array($term, $existingTerms)) {
                $statement = $this->mysqlConnector->prepareStatement("INSERT INTO articles_element_terms (element_id, term_id) VALUES (?, ?)");
                $termId = $term->getId();
                $elementId = $this->_element->getId();
                $statement->bind_param('ii', $elementId, $termId);
                $this->mysqlConnector->executeStatement($statement);
            }
        }
    }

    private function removeTerm(ArticleTerm $term): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM articles_element_terms WHERE element_id = ? AND term_id = ?");
        $elementId = $this->_element->getId();
        $termId = $term->getId();
        $statement->bind_param('ii', $elementId, $termId);
        $this->mysqlConnector->executeStatement($statement);
    }

}