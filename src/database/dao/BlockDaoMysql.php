<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\authentication\Authenticator;
use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\blocks\model\BlockPosition;
use Pageflow\Core\modules\pages\model\Page;

class BlockDaoMysql implements BlockDao {

    public static string $myAllColumns = "e.id, e.template_id, e.last_modified, e.name, e.title, e.published, e.scope_id, 
                      e.created_at, e.created_by, e.type, e.version, b.position_id";
    private static ?BlockDaoMysql $instance = null;
    private ElementHolderDao $elementHolderDao;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->elementHolderDao = ElementHolderDaoMysql::getInstance();
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): BlockDaoMysql {
        if (!self::$instance) {
            self::$instance = new BlockDaoMysql();
        }
        return self::$instance;
    }

    public function getAllBlocks(): array {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, blocks b WHERE e.id = b.element_holder_id";
        $result = $this->mysqlConnector->executeQuery($query);
        $blocks = array();
        while ($row = $result->fetch_assoc()) {
            $blocks[] = Block::constructFromRecord($row);
        }
        return $blocks;
    }

    public function getBlocksByPosition(BlockPosition $position): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT " . self::$myAllColumns . " FROM
                                                                    element_holders e, blocks b WHERE b.position_id = ?
                                                                    AND e.id = b.element_holder_id");
        $position_id = $position->getId();
        $statement->bind_param("i", $position_id);
        $result = $this->mysqlConnector->executeStatement($statement);
        $blocks = array();
        while ($row = $result->fetch_assoc()) {
            $blocks[] = Block::constructFromRecord($row);
        }
        return $blocks;
    }

    public function getBlocksWithoutPosition(): array {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, blocks b WHERE b.position_id IS  
                     NULL AND e.id = b.element_holder_id";
        $result = $this->mysqlConnector->executeQuery($query);
        $blocks = array();
        while ($row = $result->fetch_assoc()) {
            $blocks[] = Block::constructFromRecord($row);
        }
        return $blocks;
    }

    public function getBlocksByPageAndPosition(Page $page, string $positionName): array {
        $query = "SELECT " . self::$myAllColumns .
            " FROM element_holders e, blocks b, blocks_pages bp, block_positions bps" .
            " WHERE e.id = b.element_holder_id" .
            " AND bp.block_id = e.id" .
            " AND bp.page_id = " . $page->getId() .
            " AND bps.name = '" . $positionName . "'" .
            " AND b.position_id = bps.id";
        $result = $this->mysqlConnector->executeQuery($query);
        $blocks = array();
        while ($row = $result->fetch_assoc()) {
            $blocks[] = Block::constructFromRecord($row);
        }
        return $blocks;
    }

    public function getBlocksByPage(Page $page): array {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, blocks b, blocks_pages bps WHERE e.id = b.element_holder_id
                      AND bps.page_id = " . $page->getId() . " AND bps.block_id = e.id";
        $result = $this->mysqlConnector->executeQuery($query);
        $blocks = array();

        while ($row = $result->fetch_assoc()) {
            $blocks[] = Block::constructFromRecord($row);
        }
        return $blocks;
    }

    public function getBlockPositions(): array {
        $query = "SELECT * FROM block_positions ORDER BY name";
        $result = $this->mysqlConnector->executeQuery($query);
        $positions = array();
        while ($row = $result->fetch_assoc()) {
            $positions[] = BlockPosition::constructFromRecord($row);
        }
        return $positions;
    }

    public function getBlockPosition(int $positionId): ?BlockPosition {
        if (!is_null($positionId) && $positionId != '') {
            $query = "SELECT * FROM block_positions WHERE id = " . $positionId;
            $result = $this->mysqlConnector->executeQuery($query);
            while ($row = $result->fetch_assoc()) {
                return BlockPosition::constructFromRecord($row);
            }
        }
        return null;
    }

    public function getBlock(int $id): ?Block {
        $query = "SELECT " . self::$myAllColumns . " FROM element_holders e, blocks b WHERE e.id = " . $id
            . " AND e.id = b.element_holder_id";
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return Block::constructFromRecord($row);
        }
        return null;
    }

    public function createBlock(): Block {
        $newBlock = new Block();
        $newBlock->setPublished(false);
        $newBlock->setTitle('Nieuw block');
        $newBlock->setName('Nieuw block');
        $newBlock->setCreatedById(Authenticator::getCurrentUser()->getId());
        $this->persistBlock($newBlock);
        return $newBlock;
    }

    private function persistBlock(Block $block): void {
        $this->elementHolderDao->persist($block);
        $query = "INSERT INTO blocks (position_id, element_holder_id) VALUES (NULL, " . $block->getId() . ")";
        $this->mysqlConnector->executeQuery($query);
    }

    public function updateBlock(Block $block): void {
        $query = "UPDATE blocks SET";
        if ($block->getPositionId() != '' && !is_null($block->getPositionId())) {
            $query = $query . " position_id = " . $block->getPositionId();
        } else {
            $query = $query . " position_id = NULL";
        }
        $query .= " WHERE element_holder_id = " . $block->getId();
        $this->mysqlConnector->executeQuery($query);
        $this->elementHolderDao->update($block);
    }

    public function deleteBlock(Block $block): void {
        $this->elementHolderDao->delete($block);
    }

    public function createBlockPosition(): BlockPosition {
        $newPosition = new BlockPosition();
        $postfix = 1;
        while (!is_null($this->getBlockPositionByName($newPosition->getName()))) {
            $newPosition->setName("nieuwe_positie_" . $postfix);
            $postfix++;
        }
        $this->persistBlockPosition($newPosition);
        return $newPosition;
    }

    public function getBlockPositionByName(string $positionName): ?BlockPosition {
        if (!is_null($positionName) && $positionName != '') {
            $query = "SELECT * FROM block_positions WHERE name = '" . $positionName . "'";
            $result = $this->mysqlConnector->executeQuery($query);
            while ($row = $result->fetch_assoc()) {
                return BlockPosition::constructFromRecord($row);
            }
        }
        return null;
    }

    private function persistBlockPosition(BlockPosition $position): void {
        $query = "INSERT INTO block_positions (name, explanation) VALUES  ('" . $position->getName() . "', NULL)";
        $this->mysqlConnector->executeQuery($query);
        $position->setId($this->mysqlConnector->getInsertId());
    }

    public function updateBlockPosition(BlockPosition $position): void {
        $query = "UPDATE block_positions SET name = '" . $position->getName() . "'
                     , explanation = '" . $position->getExplanation() . "' WHERE id = " . $position->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function deleteBlockPosition(BlockPosition $position): void {
        $query = "DELETE FROM block_positions WHERE id = " . $position->getId();
        $this->mysqlConnector->executeQuery($query);
    }

    public function addBlockToPage(int $blockId, Page $page): void {
        $query = "INSERT INTO blocks_pages (page_id, block_id) VALUES (" . $page->getId() . ", " . $blockId . ")";
        $this->mysqlConnector->executeQuery($query);
    }

    public function deleteBlockFromPage(int $blockId, Page $page): void {
        $query = "DELETE FROM blocks_pages WHERE page_id = " . $page->getId() . "
                      AND block_id = " . $blockId;
        $this->mysqlConnector->executeQuery($query);
    }
}