<?php

namespace Pageflow\Core\modules\webforms;

use Pageflow\Core\core\model\Module;
use Pageflow\Core\modules\webforms\visuals\webforms\WebformTab;
use Pageflow\Core\view\views\ActionButtonAdd;
use Pageflow\Core\view\views\ActionButtonDelete;
use Pageflow\Core\view\views\ActionButtonSave;
use Pageflow\Core\view\views\ModuleVisual;
use Pageflow\Core\view\views\TabMenu;


class WebFormsModuleVisual extends ModuleVisual {

    private static int $FORMS_TAB = 0;
    private WebformRequestHandler $webformRequestHandler;
    private int $currentTabId = 0;

    public function __construct(Module $module) {
        parent::__construct($module);
        $this->webformRequestHandler = new WebformRequestHandler();
    }

    public function getTemplateFilename(): string {
        return "webforms/templates/root.tpl";
    }

    public function load(): void {
        $content = null;
        if ($this->currentTabId == self::$FORMS_TAB) {
            $content = new WebformTab($this->webformRequestHandler);
        }
        $this->assign("content", $content->render());
    }

    public function getActionButtons(): array {
        $actionButtons = array();
        if ($this->currentTabId == self::$FORMS_TAB) {
            $saveButton = null;
            $deleteButton = null;
            if (!is_null($this->webformRequestHandler->getCurrentWebForm())) {
                $saveButton = new ActionButtonSave('update_webform');
                $deleteButton = new ActionButtonDelete('delete_webform');
            }
            $actionButtons[] = $saveButton;
            $actionButtons[] = new ActionButtonAdd('add_webform');
            $actionButtons[] = $deleteButton;
        }
        return $actionButtons;
    }

    public function renderStyles(): array {
        $styles = array();
        $styles[] = $this->getTemplateEngine()->fetch("webforms/templates/styles/webforms.css.tpl");
        return $styles;
    }

    public function renderScripts(): array {
        $scripts = array();
        $scripts[] = $this->getTemplateEngine()->fetch("webforms/templates/scripts/module_webform.js.tpl");
        return $scripts;
    }

    public function getRequestHandlers(): array {
        $requestHandlers = array();
        $requestHandlers[] = $this->webformRequestHandler;
        return $requestHandlers;
    }

    public function onRequestHandled(): void {}

    public function loadTabMenu(TabMenu $tabMenu): int {
        $tabMenu->addItem("webforms_tab_forms", self::$FORMS_TAB);
        return $this->getCurrentTabId();
    }

}