<?php

namespace Pageflow\Core\elements\element_container_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\elements\element_container_element\ElementContainerElement;

class ElementContainerElementMetadataDao extends ElementMetadataDao {

    private static string $myAllElementColumns = "e.id, e.follow_up, e.template_id, e.include_in_table_of_contents, t.classname, t.scope_id, t.identifier, 
                                        t.domain_object, e.element_holder_id, e.element_container_id";

    private MysqlConnector $mysqlConnector;

    public function __construct(ElementContainerElement $element) {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "element_container_elements_metadata";
    }

    public function constructMetaData(array $record, Element $element): void {
        $element->setTitle($record['title']);
    }

    public function update(Element $element): void {
        $title = $element->getTitle() !== null ? "'" . addslashes($element->getTitle()) . "'" : "NULL";
        $query = "UPDATE element_container_elements_metadata SET title = " . $title . " WHERE element_id = " . $element->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function insert(Element $element): void {
        $title = $element->getTitle() !== null ? "'" . addslashes($element->getTitle()) . "'" : "NULL";
        $query = "INSERT INTO element_container_elements_metadata (title, element_id) VALUES (" . $title . ", " . $element->getId() . ")";
        $this->mysqlConnector->executeQuery($query);
    }

    public function getChildElements(int $containerElementId): array {
        $result = $this->mysqlConnector->executeQuery("SELECT " . self::$myAllElementColumns . " FROM elements e, element_types t WHERE e.element_container_id 
                                    = " . $containerElementId . " AND t.id = e.type_id ORDER BY e.follow_up ASC, e.id");
        $elements = array();
        while ($row = $result->fetch_assoc()) {
            $elements[] = Element::constructFromRecord($row);
        }
        return $elements;
    }

    public function detachChildElements(int $containerElementId): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE elements SET element_container_id = NULL WHERE element_container_id = ?");
        $statement->bind_param('i', $containerElementId);
        $this->mysqlConnector->executeStatement($statement);
    }

}
