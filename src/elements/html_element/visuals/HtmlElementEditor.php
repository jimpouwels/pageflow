<?php

namespace Pageflow\Core\elements\html_element\visuals;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\html_element\HtmlElement;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\TextArea;

class HtmlElementEditor extends ElementVisual {

    private static string $TEMPLATE = "html_element/templates/html_element_form.tpl";

    private HtmlElement $htmlElement;

    public function __construct(HtmlElement $htmlElement) {
        parent::__construct();
        $this->htmlElement = $htmlElement;
    }

    public function getElement(): Element {
        return $this->htmlElement;
    }

    public function getElementFormTemplateFilename(): string {
        return self::$TEMPLATE;
    }

    public function loadElementForm(TemplateData $data): void {
        $markupField = new TextArea('element_' . $this->htmlElement->getId() . '_markup', $this->getTextResource("html_element_editor_markup"), $this->htmlElement->getMarkup(), false, false, "html_element_markup");

        $data->assign("markup_field", $markupField->render());
    }
}
