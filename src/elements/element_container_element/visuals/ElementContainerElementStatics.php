<?php

namespace Pageflow\Core\elements\element_container_element\visuals;

use Pageflow\Core\view\views\ElementStatic;

class ElementContainerElementStatics extends ElementStatic {

    public function __construct() {
        parent::__construct();
    }

    public function renderStyles(): array {
        $styles = array();
        $styles[] = $this->getTemplateEngine()->fetch("element_container_element/templates/styles/element_container_element.css.tpl");
        return $styles;
    }

    public function renderScripts(): array {
        return array();
    }

}
