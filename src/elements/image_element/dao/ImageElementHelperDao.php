<?php

namespace Pageflow\Core\elements\image_element\dao;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\database\MysqlConnector;

class ImageElementHelperDao {

    private MysqlConnector $mysqlConnector;

    public function __construct() {
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public function getElementHoldersWhereImageIsUsed(int $imageId): array {
        $query = "SELECT eh.* FROM element_holders eh
                  JOIN elements e ON e.element_holder_id = eh.id
                  JOIN image_elements_metadata iem ON iem.element_id = e.id
                  WHERE iem.image_id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $imageId);
        $result = $this->mysqlConnector->executeStatement($statement);
        $elementHolders = array();
        while ($row = $result->fetch_assoc()) {
            $elementHolders[] = ElementHolder::constructFromRecord($row);
        }
        return $elementHolders;
    }
}