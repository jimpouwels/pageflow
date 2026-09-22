<?php

namespace Pageflow\Core\elements\element_container_element;

use Pageflow\Core\core\form\FormException;
use Pageflow\Core\request_handlers\ElementForm;

class ElementContainerElementForm extends ElementForm {

    private ElementContainerElement $elementContainerElement;

    public function __construct(ElementContainerElement $elementContainerElement) {
        parent::__construct($elementContainerElement);
        $this->elementContainerElement = $elementContainerElement;
    }

    public function loadFields(): void {
        $elementId = $this->elementContainerElement->getId();
        $title = $this->getFieldValue('element_' . $elementId . '_title');
        if ($this->hasErrors()) {
            throw new FormException();
        } else {
            parent::loadFields();
            $this->elementContainerElement->setTitle($title);
        }
    }
}
