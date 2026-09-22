<?php

namespace Pageflow\Core\core\form;

use Pageflow\Core\core\model\ElementHolder;
use const Pageflow\Core\ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID;

class ElementHolderForm extends Form {

    private ElementHolder $elementHolder;

    public function __construct(ElementHolder $elementHolder) {
        $this->elementHolder = $elementHolder;
    }

    public function loadFields(): void {
        $itemOrder = $this->getFieldValue('draggable_order');
        $itemOrderArr = array();
        if ($itemOrder) {
            $itemOrderArr = explode(',', $itemOrder);
        }
        $orderNr = 0;
        foreach ($itemOrderArr as $item) {
            foreach ($this->elementHolder->getElements() as $element) {
                if ($element->getId() == $item) {
                    $element->setOrderNr($orderNr++);
                    break;
                }
            }
        }

        $this->loadContainerAssignments();
    }

    public function loadContainerAssignments(): void {
        $assignmentsJson = $this->getFieldValue(ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID);
        $assignments = array();
        if ($assignmentsJson) {
            $decoded = json_decode($assignmentsJson, true);
            if (is_array($decoded)) {
                $assignments = $decoded;
            }
        }
        foreach ($this->elementHolder->getElements() as $element) {
            if (!array_key_exists($element->getId(), $assignments)) {
                // Keep current DB-backed value when an element id is not present in posted assignments.
                continue;
            }
            $containerId = $assignments[$element->getId()];
            $element->setContainerId($containerId !== null && $containerId !== '' ? (int)$containerId : null);
        }
    }

}