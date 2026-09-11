<?php

namespace Pageflow\Core\elements\separator_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;

class SeparatorElementMetadataDao extends ElementMetadataDao
{

    private MysqlConnector $mysqlConnector;

    public function __construct(Element $element)
    {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string
    {
        return "separator_elements_metadata";
    }

    public function constructMetaData(array $record, $element): void
    {
        $element->setTitle($record['title']);
        $element->setHtmlId($record['html_id'] ?? null);
    }

    public function update(Element $element): void
    {
        $query = "UPDATE separator_elements_metadata SET title = '" . $element->getTitle() . "', html_id = " . ($element->getHtmlId() ? "'" . $element->getHtmlId() . "'" : "NULL") . " WHERE element_id = " . $element->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function insert(Element $element): void
    {
        $query = "INSERT INTO separator_elements_metadata (title, html_id, element_id) VALUES ('" . $element->getTitle() . "', " . ($element->getHtmlId() ? "'" . $element->getHtmlId() . "'" : "NULL") . ", " . $element->getId() . ")";
        $this->mysqlConnector->executeQuery($query);
    }

}