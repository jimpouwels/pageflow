<?php

namespace Pageflow\Core\elements\iframe_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;

class IFrameElementMetadataDao extends ElementMetadataDao
{

    private MysqlConnector $mysqlConnector;

    public function __construct(Element $element)
    {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string
    {
        return "iframe_elements_metadata";
    }

    public function constructMetaData(array $record, $element): void
    {
        $element->setTitle($record['title']);
        $element->setUrl($record['url']);
        $element->setWidth($record['width']);
        $element->setHeight($record['height']);
    }

    public function update(Element $element): void
    {
        $query = "UPDATE iframe_elements_metadata SET `url` = '" . $element->getUrl() . "', title = '" . $element->getTitle() . "', width = " . ($element->getWidth() ? $element->getWidth() : "NULL") . ", height = " . ($element->getHeight() ? $element->getHeight() : "NULL") .
            " WHERE element_id = " . $element->getId();

        $this->mysqlConnector->executeQuery($query);
    }

    public function insert(Element $element): void
    {
        $query = "INSERT INTO iframe_elements_metadata (title, `url`, width, height, element_id) VALUES "
            . "('" . $element->getTitle() . "', '" . $element->getUrl() . "', 0 , 0, " . $element->getId() . ")";
        $this->mysqlConnector->executeQuery($query);
    }

}
