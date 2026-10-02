<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\core\model\Module;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\visuals\template_variants\TemplateVariantEditorTab;
use Pageflow\Core\modules\templates\visuals\templates\TemplatesTab;
use Pageflow\Core\view\views\ActionButtonAdd;
use Pageflow\Core\view\views\ActionButtonDelete;
use Pageflow\Core\view\views\ActionButtonReload;
use Pageflow\Core\view\views\ActionButtonSave;
use Pageflow\Core\view\views\ModuleVisual;
use Pageflow\Core\view\views\TabMenu;

class TemplateModuleVisual extends ModuleVisual {
    private static int $TEMPLATES_VARIANTS_TAB = 0;
    private static int $TEMPLATE_TAB = 1;

    private ?TemplateVariant $currentTemplateVariant;
    private ?Scope $currentScope;
    private Module $module;
    private TemplateEditorRequestHandler $templateEditorRequestHandler;
    private TemplateRequestHandler $templateRequestHandler;

    public function __construct(Module $module) {
        parent::__construct($module);
        $this->module = $module;
        $this->templateEditorRequestHandler = new TemplateEditorRequestHandler();
        $this->templateRequestHandler = new TemplateRequestHandler();
    }

    public function getTemplateFilename(): string {
        return "templates/templates/root.tpl";
    }

    public function load(): void {
        if ($this->getCurrentTabId() == self::$TEMPLATES_VARIANTS_TAB) {
            $content = new TemplateVariantEditorTab($this->currentTemplateVariant, $this->currentScope, $this->templateEditorRequestHandler->getShowUnassigned());
        } else {
            $content = new TemplatesTab($this->templateRequestHandler);
        }
        $this->assign("content", $content->render());
    }

    public function getActionButtons(): array {
        $actionButtons = array();
        if ($this->getCurrentTabId() == self::$TEMPLATES_VARIANTS_TAB) {
            if ($this->currentTemplateVariant) {
                $actionButtons[] = new ActionButtonSave('update_template_variant');
            }
            $actionButtons[] = new ActionButtonAdd('add_template_variant');
            if ($this->currentTemplateVariant) {
                $actionButtons[] = new ActionButtonDelete('delete_template_variant');
            }
        } else if ($this->getCurrentTabId() == self::$TEMPLATE_TAB) {
            if ($this->templateRequestHandler->getCurrentTemplate()) {
                $actionButtons[] = new ActionButtonSave('update_template');
            }
            $actionButtons[] = new ActionButtonAdd('add_template');
            if ($this->templateRequestHandler->getCurrentTemplate()) {
                $actionButtons[] = new ActionButtonReload('reload_template');
                $actionButtons[] = new ActionButtonDelete('delete_template');
            }
        }
        return $actionButtons;
    }

    public function renderStyles(): array {
        $styles = array();
        $styles[] = $this->getTemplateEngine()->fetch("templates/templates/styles/templates.css.tpl");
        return $styles;
    }

    public function renderScripts(): array {
        $scripts = array();
        $scripts[] = $this->getTemplateEngine()->fetch("templates/templates/scripts/templates.js.tpl");
        return $scripts;
    }

    public function getRequestHandlers(): array {
        $requestHandlers = array();
        $requestHandlers[] = $this->templateEditorRequestHandler;
        $requestHandlers[] = $this->templateRequestHandler;
        return $requestHandlers;
    }

    public function onRequestHandled(): void {
        $this->currentTemplateVariant = $this->templateEditorRequestHandler->getCurrentTemplateVariant();
        $this->currentScope = $this->templateEditorRequestHandler->getCurrentScope();
    }

    public function getTitle(): string {
        return $this->getTextResource($this->module->getIdentifier() . '_module_title');
    }

    public function loadTabMenu(TabMenu $tabMenu): int {
        $tabMenu->addItem("templates_tab_menu_template_variants", self::$TEMPLATES_VARIANTS_TAB);
        $tabMenu->addItem("templates_tab_menu_templates", self::$TEMPLATE_TAB);
        return $this->getCurrentTabId();
    }
}