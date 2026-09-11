<?php

namespace Pageflow\Core\elements\photo_album_element\dao;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\database\MysqlConnector;

class PhotoAlbumElementMetadataDao extends ElementMetadataDao
{

    private MysqlConnector $mysqlConnector;
    private Element $element;

    public function __construct(Element $element) {
        parent::__construct($element);
        $this->element = $element;
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getTableName(): string {
        return "photo_album_elements_metadata";
    }

    public function constructMetaData(array $record, $element): void {
        $element->setTitle($record['title']);
        $element->setNumberOfResults($record['number_of_results']);
        $element->setImageIds($this->getImageIds($element));
    }

    public function update(Element $element): void {
        $query = "UPDATE photo_album_elements_metadata SET title = ?, ";
        if (!$element->getNumberOfResults()) {
            $query = $query . "number_of_results = NULL ";
        } else {
            $query = $query . "number_of_results = " . $element->getNumberOfResults();
        }
        $query = $query . " WHERE element_id = " . $element->getId();
        $statement = $this->mysqlConnector->prepareStatement($query);
        $title = $element->getTitle();
        $statement->bind_param('s', $title);

        $this->mysqlConnector->executeStatement($statement);
        $this->addImageIdsIfNotExists($element);
    }

    public function insert(Element $element): void {
        $statement = null;
        $query = "INSERT INTO photo_album_elements_metadata (title, element_id, number_of_results) VALUES
                    (?, ?, NULL)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $title = $element->getTitle();
        $id = $element->getId();
        $statement->bind_param('si', $title, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    private function getImageIds(Element $element): array {
        $query = "SELECT image_id FROM photo_album_element_images 
                  WHERE photo_album_element_id = ? 
                  ORDER BY display_order ASC, id ASC";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $elementId = $element->getId();
        $statement->bind_param('i', $elementId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $imageIds = array();
        while ($row = $result->fetch_assoc()) {
            $imageIds[] = $row['image_id'];
        }
        return $imageIds;
    }

    private function addImageIdsIfNotExists(Element $element): void {
        $existingImageIds = $this->getImageIds($element);
        foreach ($existingImageIds as $existingImageId) {
            if (!in_array($existingImageId, $this->element->getImageIds()))
                $this->removeImageId($element, $existingImageId);
        }

        $elementId = $element->getId();
        $order = 0;
        foreach ($this->element->getImageIds() as $imageId) {
            if (!in_array($imageId, $existingImageIds)) {
                $query = "INSERT INTO photo_album_element_images (image_id, photo_album_element_id, display_order) VALUES (?, ?, ?)";
                $statement = $this->mysqlConnector->prepareStatement($query);
                $statement->bind_param('iii', $imageId, $elementId, $order);
                $this->mysqlConnector->executeStatement($statement);
            } else {
                // Update order for existing images
                $query = "UPDATE photo_album_element_images SET display_order = ? WHERE photo_album_element_id = ? AND image_id = ?";
                $statement = $this->mysqlConnector->prepareStatement($query);
                $statement->bind_param('iii', $order, $elementId, $imageId);
                $this->mysqlConnector->executeStatement($statement);
            }
            $order++;
        }
    }

    private function removeImageId(Element $element, int $imageId): void {
        $query = "DELETE FROM photo_album_element_images WHERE photo_album_element_id = ? AND image_id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $elementId = $element->getId();
        $statement->bind_param('ii', $elementId, $imageId);
        $this->mysqlConnector->executeStatement($statement);
    }
}