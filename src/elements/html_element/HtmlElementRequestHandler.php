<?php

namespace Pageflow\Core\elements\html_element;

use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\request_handlers\HttpRequestHandler;

class HtmlElementRequestHandler extends HttpRequestHandler {

    private HtmlElement $htmlElement;
    private ElementDao $elementDao;
    private HtmlElementForm $htmlElementForm;

    public function __construct(HtmlElement $htmlElement) {
        $this->htmlElement = $htmlElement;
        $this->elementDao = ElementDaoMysql::getInstance();
        $this->htmlElementForm = new HtmlElementForm($this->htmlElement);
    }

    public function handleGet(): void {}

    public function handlePost(): void {
        $this->htmlElementForm->loadFields();
        $this->elementDao->updateElement($this->htmlElement);
    }
}
