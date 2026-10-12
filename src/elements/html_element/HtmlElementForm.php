<?php

namespace Pageflow\Core\elements\html_element;

use Pageflow\Core\core\form\FormException;
use Pageflow\Core\request_handlers\ElementForm;

class HtmlElementForm extends ElementForm {

    private HtmlElement $htmlElement;

    public function __construct(HtmlElement $htmlElement) {
        parent::__construct($htmlElement);
        $this->htmlElement = $htmlElement;
    }

    public function loadFields(): void {
        parent::loadFields();
        $this->htmlElement->setMarkup($this->getFieldValue('element_' . $this->htmlElement->getId() . '_markup'));
        if ($this->hasErrors()) {
            throw new FormException();
        }
    }

}
