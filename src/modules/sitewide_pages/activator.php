<?php

namespace Pageflow\Core\modules\sitewide_pages;

use Pageflow\Core\core\model\Module;
use Pageflow\Core\modules\sitewide_pages\visuals\SitewidePageList;
use Pageflow\Core\view\views\ActionButtonDelete;
use Pageflow\Core\view\views\ModuleVisual;
use Pageflow\Core\view\views\TabMenu;

class SitewidePagesModuleVisual extends ModuleVisual {

    private SitewidePagesRequestHandler $requestHandler;

    public function __construct(Module $module) {
        parent::__construct($module);
        $this->requestHandler = new SitewidePagesRequestHandler();
    }

    public function getTemplateFilename(): string {
        return "sitewide_pages/templates/root.tpl";
    }

    public function load(): void {
        $list = new SitewidePageList();
        $this->assign("list", $list->render());
    }

    public function getActionButtons(): array {
        $buttons = array();
        $buttons[] = new ActionButtonDelete('remove_sitewide_pages');
        return $buttons;
    }

    public function renderStyles(): array {
        $styles = array();
        $styles[] = $this->getTemplateEngine()->fetch("sitewide_pages/templates/styles/sitewide_pages.css.tpl");
        return $styles;
    }

    public function renderScripts(): array {
        $scripts = array();
        $scripts[] = $this->getTemplateEngine()->fetch("sitewide_pages/templates/scripts/module_sitewide_pages.js.tpl");
        return $scripts;
    }

    public function getRequestHandlers(): array {
        $requestHandlers = array();
        $requestHandlers[] = $this->requestHandler;
        return $requestHandlers;
    }

    public function onRequestHandled(): void {
    }

    public function loadTabMenu(TabMenu $tabMenu): int {
        return 0;
    }

}