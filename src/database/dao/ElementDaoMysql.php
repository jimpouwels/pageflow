<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\core\model\ElementType;
use Pageflow\Core\database\MysqlConnector;

class ElementDaoMysql implements ElementDao {

    private static string $myAllColumns = "e.id, e.follow_up, e.template_id, e.include_in_table_of_contents, t.classname, t.scope_id, t.identifier, 
                                        t.domain_object, e.element_holder_id, e.element_container_id";
    private static ?ElementDaoMysql $instance = null;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): ElementDaoMysql {
        if (!self::$instance) {
            self::$instance = new ElementDaoMysql();
        }
        return self::$instance;
    }

    public function getElements(ElementHolder $elementHolder): array {
        $result = $this->mysqlConnector->executeQuery("SELECT " . self::$myAllColumns . " FROM elements e, element_types t WHERE element_holder_id 
                                    = " . $elementHolder->getId() . " AND t.id = e.type_id ORDER BY e.follow_up ASC, e.id");
        $elements = array();
        while ($row = $result->fetch_assoc()) {
            $elements[] = Element::constructFromRecord($row);
        }
        return $elements;
    }

    public function getElement(int $id): ?Element {
        $query = "SELECT " . self::$myAllColumns . " FROM elements e, element_types t WHERE e.id = " . $id . " 
                      AND e.type_id = t.id;";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return Element::constructFromRecord($row);
        }
        return null;
    }

    public function updateElement(Element $element): void {
        $set = 'follow_up = ' . $element->getOrderNr();
        if (!is_null($element->getTemplateId()) && $element->getTemplateId() != '') {
            $set .= ', template_id = ' . $element->getTemplateId();
        } else {
            $set .= ', template_id = NULL';
        }
        $set .= ', include_in_table_of_contents = ' . ($element->includeInTableOfContents() ? '1' : '0');
        $set .= ', element_container_id = ' . (!is_null($element->getContainerId()) ? $element->getContainerId() : 'NULL');
        $query = "UPDATE elements SET " . $set . "    WHERE id = " . $element->getId();
        $this->mysqlConnector->executeQuery($query);
        $element->updateMetaData();
    }

    public function deleteElement(Element $element): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM elements WHERE id = ?");
        $elementId = $element->getId();
        $statement->bind_param('i', $elementId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getElementTypes(): array {
        $query = "SELECT * FROM element_types ORDER BY identifier";
        $result = $this->mysqlConnector->executeQuery($query);
        $elementTypes = array();
        while ($row = $result->fetch_assoc()) {
            $elementTypes[] = ElementType::constructFromRecord($row);
        }
        return $elementTypes;
    }

    public function updateElementType(ElementType $elementType): void {
        $query = "UPDATE element_types SET classname = '" . $elementType->getClassName() . "', domain_object = '" . $elementType->getDomainObject() . "', scope_id = " .
            $elementType->getScopeId() . ", identifier = '" . $elementType->getIdentifier() . "', system_default = " .
            ($elementType->getSystemDefault() ? 1 : 0) . " WHERE identifier = '" . $elementType->getIdentifier() . "'";
        $this->mysqlConnector->executeQuery($query);
    }

    public function persistElementType(ElementType $elementType): void {
        $query = "INSERT INTO element_types (classname, domain_object, scope_id, identifier, system_default)" .
            " VALUES ('" . $elementType->getClassName() . "', '" . $elementType->getDomainObject() . "', " . $elementType->getScopeId() . ", " .
            "'" . $elementType->getIdentifier() . "', " . ($elementType->getSystemDefault() ? 1 : 0) . ")";
        $this->mysqlConnector->executeQuery($query);
        $elementType->setId($this->mysqlConnector->getInsertId());
    }

    public function deleteElementType(ElementType $elementType): void {
        $query = "DELETE FROM element_types WHERE id = " . $elementType->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function getElementType(int $elementTypeId): ?ElementType {
        $query = "SELECT * FROM element_types WHERE id = " . $elementTypeId;
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return ElementType::constructFromRecord($row);
        }
        return null;
    }

    public function getElementTypeByIdentifier(string $identifier): ?ElementType {
        $query = "SELECT * FROM element_types WHERE identifier = '$identifier'";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return ElementType::constructFromRecord($row);
        }
        return null;
    }

    public function getElementTypeForElement(int $elementId): ?ElementType {
        $query = "SELECT * FROM element_types t, elements e WHERE e.id = " . $elementId . " AND t.id = e.type_id";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return ElementType::constructFromRecord($row);
        }
        return null;
    }

    public function insertElement(ElementType $elementType, Element $element): Element {
        $containerId = !is_null($element->getContainerId()) ? $element->getContainerId() : 'NULL';
        $query = "INSERT INTO elements(follow_up, type_id, element_holder_id, element_container_id) VALUES (" . $element->getOrderNr() . " 
                      , " . $elementType->getId() . ", " . $element->getElementHolderId() . ", " . $containerId . ")";
        $this->mysqlConnector->executeQuery($query);
        $element->setId($this->mysqlConnector->getInsertId());
        $element->updateMetaData();
        return $element;
    }

}