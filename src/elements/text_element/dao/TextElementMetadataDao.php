<?php

namespace Pageflow\Core\elements\text_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\elements\text_element\TextElement;

class TextElementMetadataDao extends ElementMetadataDao
{

    private MysqlConnector $mysqlConnector;

    public function __construct(TextElement $element) {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "text_elements_metadata";
    }

    public function constructMetaData(array $record, Element $element): void {
        $element->setTitle($record['title']);
        $element->setText($record['text']);
    }

    public function update(Element $element): void {
        $title = $element->getTitle();
        $text = $element->getText();
        $elementId = $element->getId();
        $query = "UPDATE text_elements_metadata SET title = ?, text = ? WHERE element_id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('ssi', $title, $text, $elementId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function insert(Element $element): void {
        $title = $element->getTitle();
        $text = $element->getText();
        $elementId = $element->getId();
        $query = "INSERT INTO text_elements_metadata (title, `text`, element_id) VALUES (?, ?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('ssi', $title, $text, $elementId);
        $this->mysqlConnector->executeStatement($statement);
    }

}