<?php
namespace Pageflow\Core\elements\list_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\list_element\ListItem;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;

class ListElementMetaDataDao extends ElementMetadataDao
{

    private static string $myAllColumns = "i.id, i.text, i.indent, i.element_id, i.display_order, i.functional_image_id";
    private MysqlConnector $mysqlConnector;

    public function __construct(Element $element) {
        parent::__construct($element);
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "list_elements_metadata";
    }

    public function constructMetaData(array $record, $element): void {
        $element->setTitle($record['title']);
        $element->setListItems($this->getListItems($element));
    }

    public function deleteListItem(ListItem $list_item): void {
        $query = "DELETE FROM list_element_items WHERE id = " . $list_item->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function update(Element $element): void {
        $query = "UPDATE list_elements_metadata SET title = '" . $element->getTitle() . "' WHERE element_id = " . $element->getId();
        $this->mysqlConnector->executeQuery($query);

        $this->storeItems($element->getListItems());
    }

    public function insert(Element $element): void {
        $query = "INSERT INTO list_elements_metadata (title, element_id) VALUES 
                        ('" . $element->getTitle() . "', " . $element->getId() . ")";
        $this->mysqlConnector->executeQuery($query);
    }

    private function storeItems(array $items): void {
        foreach ($items as $listItem) {
            if ($listItem->getId()) {
                $this->updateListItem($listItem);
            } else {
                $this->insertListItem($listItem);
            }
        }
    }

    private function getListItems(Element $element): array {
        $query = "SELECT " . self::$myAllColumns . " FROM list_element_items i, elements e WHERE i.element_id = " . $element->getId() .
            " AND e.id = " . $element->getId() . " ORDER BY i.display_order ASC, i.id ASC";
        $result = $this->mysqlConnector->executeQuery($query);
        $listItems = array();
        while ($row = $result->fetch_assoc()) {
            $listItem = new ListItem();
            $listItem->setId($row['id']);
            $listItem->setText($row['text']);
            $listItem->setIndent($row['indent']);
            $listItem->setElementId($row['element_id']);
            $listItem->setOrderNr($row['display_order']);
            $listItem->setFunctionalImageId(isset($row['functional_image_id']) ? (int)$row['functional_image_id'] : null);

            $listItems[] = $listItem;
        }

        return $listItems;
    }

    private function updateListItem(mixed $listItem): void {
        $query = "UPDATE list_element_items SET `text` = ?, indent = ?, display_order = ?, functional_image_id = ? WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $indent = $listItem->getIndent();
        $id = $listItem->getId();
        $text = $listItem->getText();
        $displayOrder = $listItem->getOrderNr();
        $functionalImageId = $listItem->getFunctionalImageId();
        $statement->bind_param('siiii', $text, $indent, $displayOrder, $functionalImageId, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    private function insertListItem(mixed $listItem): void {
        $query = "INSERT INTO list_element_items (`text`, indent, element_id, display_order) VALUES ('', ?, ?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $indent = $listItem->getIndent();
        $elementId = $this->getElement()->getId();
        $displayOrder = $listItem->getOrderNr();
        $statement->bind_param('iii', $indent, $elementId, $displayOrder);
        $this->mysqlConnector->executeStatement($statement);
        $listItem->setId($this->mysqlConnector->getInsertId());
    }

}