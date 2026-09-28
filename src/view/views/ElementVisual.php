<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\utilities\StringUtility;
use Pageflow\Core\view\TemplateData;
use const Pageflow\Core\DELETE_ELEMENT_FORM_ID;

abstract class ElementVisual extends Visual {

    private ElementDao $elementDao;

    abstract function getElement(): Element;

    abstract function loadElementForm(TemplateData $data): void;

    abstract function getElementFormTemplateFilename(): string;

    public function __construct() {
        parent::__construct();
        $this->elementDao = ElementDaoMysql::getInstance();
    }

    public function getTemplateFilename(): string {
        return "element.tpl";
    }

    public function load(): void {
        $element = $this->getElement();
        $elementType = $this->elementDao->getElementTypeForElement($element->getId());

        $templatePicker = new TemplatePicker("element_" . $element->getId() . "_template", "", $element->getTemplateId(), false, "template_picker", $elementType->getScope());

        $panelContentTemplateData = $this->createChildData();
        $this->loadElementForm($panelContentTemplateData);
        $this->assign("element_form", $this->fetch($this->getElementFormTemplateFilename(), $panelContentTemplateData));
        $this->assign("index", $element->getOrderNr());
        $this->assign("id", $element->getId());
        $this->assign("icon_url", BlackBoard::getElementFileUrl($elementType->getIdentifier(), 'img/' . $elementType->getIdentifier() . '.png'));
        $this->assign("type", $this->getTextResource($elementType->getIdentifier() . '_label'));
        $this->assign("template_picker", $templatePicker->render());

        $tableOfContentsHtml = "";
        if ($elementType->getIdentifier() != 'table_of_contents_element') {
            $includeInTocField = new SingleCheckbox("element_" . $element->getId() . "_toc", $this->getTextResource("element_include_in_table_of_contents"), $element->includeInTableOfContents() ? 1 : 0, false, "element_include_in_toc");
            $tableOfContentsHtml = $includeInTocField->render();
        }
        $this->assign("include_in_table_of_contents", $tableOfContentsHtml);
        $this->assign("identifier", $elementType->getIdentifier());
        $this->assign("delete_element_form_id", DELETE_ELEMENT_FORM_ID);
        $this->assign("summary_text", StringUtility::escapeXml($element->getSummaryText()));
    }
}