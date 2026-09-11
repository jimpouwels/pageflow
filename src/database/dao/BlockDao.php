<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\blocks\model\BlockPosition;
use Pageflow\Core\modules\pages\model\Page;

interface BlockDao {
    public function getAllBlocks(): array;

    public function getBlocksByPosition(BlockPosition $position): array;

    public function getBlocksWithoutPosition(): array;

    public function getBlocksByPageAndPosition(Page $page, string $positionName): array;

    public function getBlocksByPage(Page $page): array;

    public function getBlockPositions(): array;

    public function getBlockPosition(int $positionId): ?BlockPosition;

    public function getBlock(int $id): ?Block;

    public function createBlock(): Block;

    public function updateBlock(Block $block): void;

    public function deleteBlock(Block $block): void;

    public function createBlockPosition(): BlockPosition;

    public function getBlockPositionByName(string $positionName): ?BlockPosition;

    public function updateBlockPosition(BlockPosition $position): void;

    public function deleteBlockPosition(BlockPosition $position): void;

    public function addBlockToPage(int $blockId, Page $page): void;

    public function deleteBlockFromPage(int $blockId, Page $page): void;
}