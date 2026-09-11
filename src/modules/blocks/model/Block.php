<?php

namespace Pageflow\Core\modules\blocks\model;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\core\model\ElementHolderType;
use Pageflow\Core\database\dao\BlockDaoMysql;

class Block extends ElementHolder {

    private static int $SCOPE = 6;
    private ?int $positionId = null;

    public function __construct() {
        parent::__construct(self::$SCOPE, ElementHolderType::BLOCK);
    }

    public static function constructFromRecord(array $row): Block {
        $block = new Block();
        $block->initFromDb($row);
        return $block;
    }

    protected function initFromDb(array $row): void {
        $this->setPublished($row['published'] == 1);
        $this->setPositionId($row['position_id']);
        parent::initFromDb($row);
    }

    public function getPositionId(): ?int {
        return $this->positionId;
    }

    public function setPositionId(?int $positionId): void {
        $this->positionId = $positionId;
    }

    public function getPositionName(): string {
        return $this->getPosition()?->getName() ?? "";
    }

    public function getPosition(): ?BlockPosition {
        $dao = BlockDaoMysql::getInstance();
        return $dao->getBlockPosition($this->positionId);
    }

}