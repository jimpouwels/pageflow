<?php

namespace Pageflow\Core\elements\element_container_element\visuals;

use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\elements\element_container_element\ElementContainerElement;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\TextField;

class ElementContainerElementEditor extends ElementVisual {

    private static string $TEMPLATE = "element_container_element/templates/element_container_element_form.tpl";
    private ElementContainerElement $element;
    private ElementDao $elementDao;

    public function __construct(ElementContainerElement $element) {
        parent::__construct();
        $this->element = $element;
        $this->elementDao = ElementDaoMysql::getInstance();
    }

    public function getElementFormTemplateFilename(): string {
        return self::$TEMPLATE;
    }

    public function getElement(): Element {
        return $this->element;
    }

    public function loadElementForm(TemplateData $data): void {
        $titleField = new TextField("element_" . $this->element->getId() . "_title", $this->getTextResource("element_container_element_editor_title"), $this->element->getTitle(), false, true, null);

        $childElements = $this->element->getChildElements();
        $renderedChildren = array();
        foreach ($childElements as $childElement) {
            $renderedChildren[] = $childElement->getBackendVisual()->render();
        }

        $data->assign("title_field", $titleField->render());
        $data->assign("container_id", $this->element->getId());
        $data->assign("child_elements", $renderedChildren);
        $data->assign("child_count", count($childElements));
        $data->assign("element_types", $this->getElementTypes());
    }

    private function getElementTypes(): array {
        $elementTypes = array();
        foreach ($this->elementDao->getElementTypes() as $elementType) {
            if ($elementType->getIdentifier() == 'element_container_element') {
                continue;
            }
            $typeData = array();
            $typeData['id'] = $elementType->getId();
            $typeData['name'] = $this->getTextResource($elementType->getIdentifier() . '_label');
            $typeData['identifier'] = $elementType->getIdentifier();
            $typeData['icon_url'] = BlackBoard::getElementFileUrl($elementType->getIdentifier(), 'img/' . $elementType->getIdentifier() . '.png');
            $elementTypes[] = $typeData;
        }
        return $elementTypes;
    }
}
