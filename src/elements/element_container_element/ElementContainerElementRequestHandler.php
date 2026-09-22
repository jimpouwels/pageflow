<?php

namespace Pageflow\Core\elements\element_container_element;

use Pageflow\Core\core\form\FormException;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\elements\ElementContainsErrorsException;
use Pageflow\Core\request_handlers\HttpRequestHandler;

class ElementContainerElementRequestHandler extends HttpRequestHandler {

    private ElementContainerElement $elementContainerElement;
    private ElementDao $elementDao;
    private ElementContainerElementForm $elementContainerElementForm;

    public function __construct(ElementContainerElement $elementContainerElement) {
        $this->elementContainerElement = $elementContainerElement;
        $this->elementDao = ElementDaoMysql::getInstance();
        $this->elementContainerElementForm = new ElementContainerElementForm($this->elementContainerElement);
    }

    public function handleGet(): void {}

    public function handlePost(): void {
        try {
            $this->elementContainerElementForm->loadFields();
            $this->elementDao->updateElement($this->elementContainerElement);
        } catch (FormException) {
            throw new ElementContainsErrorsException("Element container contains errors");
        }
    }
}
