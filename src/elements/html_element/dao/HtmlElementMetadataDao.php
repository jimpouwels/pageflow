<?php

namespace Pageflow\Core\elements\html_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\elements\html_element\HtmlElement;

class HtmlElementMetadataDao extends ElementMetadataDao {

    private MysqlConnector $mysqlConnector;

    public function __construct(HtmlElement $element) {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "html_elements_metadata";
    }

    public function constructMetaData(array $record, Element $element): void {
        $element->setMarkup($record['markup']);
    }

    public function update(Element $element): void {
        $markup = $element->getMarkup();
        $elementId = $element->getId();
        $query = "UPDATE html_elements_metadata SET markup = ? WHERE element_id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('si', $markup, $elementId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function insert(Element $element): void {
        $markup = $element->getMarkup();
        $elementId = $element->getId();
        $query = "INSERT INTO html_elements_metadata (markup, element_id) VALUES (?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('si', $markup, $elementId);
        $this->mysqlConnector->executeStatement($statement);
    }

}
