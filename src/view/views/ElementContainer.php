<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\view\TemplateData;

class ElementContainer extends Panel {

    private array $elements;
    private ElementDao $elementDao;

    public function __construct(array $elements) {
        parent::__construct($this->getTextResource('element_holder_content_title'), 'element_container');
        $this->elements = $elements;
        $this->elementDao = ElementDaoMysql::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "element_container.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $topLevelElements = array_filter($this->elements, fn($element) => $element->getContainerId() === null);
        if (count($topLevelElements) > 0) {
            $data->assign("elements", $this->renderElements($topLevelElements));
        }
        $data->assign("element_types", $this->getElementTypes());
        $data->assign("container_assignments_json", $this->getContainerAssignmentsJson());
    }

    private function renderElements(array $elements): array {
        $rendered = array();
        foreach ($elements as $element) {
            $rendered[] = $element->getBackendVisual()->render();
        }
        return $rendered;
    }

    private function getContainerAssignmentsJson(): string {
        $assignments = array();
        foreach ($this->elements as $element) {
            if ($element->getContainerId() !== null) {
                $assignments[$element->getId()] = $element->getContainerId();
            }
        }
        return json_encode($assignments);
    }

    private function getElementTypes(): array {
        $elementTypes = array();
        foreach ($this->elementDao->getElementTypes() as $elementType) {
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