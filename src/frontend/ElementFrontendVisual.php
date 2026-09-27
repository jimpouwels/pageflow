<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;
use const Pageflow\CMS_ROOT;

abstract class ElementFrontendVisual extends FrontendVisual {

    private Element $element;

    public function __construct(Page $page, ?Article $article, ?Block $block, Element $element) {
        parent::__construct($page, $article, $block);
        $this->element = $element;
    }

    public function getTemplateFilename(): string {
        return CMS_ROOT . "/frontend/templates/element.tpl";
    }

    public function getElementTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    abstract function loadElement(array &$data): void;

    public function loadVisual(array &$data): void {
        $this->assign("toc_reference", $this->toAnchorValue($this->element->getTitle()));
        $this->assign("include_in_table_of_contents", $this->element->includeInTableOfContents());

        $this->loadElement($data);
        if ($data) {
            foreach ($data as $key => $value) {
                $this->assign($key, $value);
            }
        }
        $this->assign("element_html", "");
        $elementTemplate = $this->getTemplateService()->getTemplateForPresentable($this->getElement());
        if ($elementTemplate) {
            $this->assign("element_html", $this->fetch($this->getFrontendTemplateDir() . "/" . $elementTemplate->getFileName()));
        }
    }

    public function getPresentable(): ?Presentable {
        return $this->element;
    }

    protected function getElement(): Element {
        return $this->element;
    }

}