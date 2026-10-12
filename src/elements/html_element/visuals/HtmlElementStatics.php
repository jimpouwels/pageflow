<?php

namespace Pageflow\Core\elements\html_element\visuals;

use Pageflow\Core\view\views\ElementStatic;

class HtmlElementStatics extends ElementStatic {

    public function __construct() {
        parent::__construct();
    }

    public function renderStyles(): array {
        $styles = array();
        $styles[] = $this->getTemplateEngine()->fetch("html_element/templates/styles/html_element.css.tpl");
        return $styles;
    }

    public function renderScripts(): array {
        return array();
    }

}
