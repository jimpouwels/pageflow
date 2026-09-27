<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;

class BlockVisual extends FrontendVisual {

    private Block $block;

    public function __construct(Block $block, Page $page, ?Article $article) {
        parent::__construct($page, $article, $block);
        $this->block = $block;
    }

    public function getTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    public function loadVisual(?array &$data): void {
        $data['id'] = $this->block->getId();
        $data['title'] =$this->block->getTitle();
        $this->renderElementHolderContent($this->block, $data);
    }

    public function getPresentable(): ?Presentable {
        return $this->block;
    }
}