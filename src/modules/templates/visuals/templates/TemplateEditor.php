<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\database\dao\ScopeDao;
use Pageflow\Core\database\dao\ScopeDaoMysl;
use Pageflow\Core\view\views\TemplatePicker;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\TextField;

class TemplateEditor extends Panel {
    private TemplateDao $templateDao;
    private ScopeDao $scopeDao;
    private Template $currentTemplate;

    public function __construct(Template $currentTemplate) {
        parent::__construct('template_editor_panel_title', 'template_editor_panel');
        $this->templateDao = TemplateDaoMysql::getInstance();
        $this->scopeDao = ScopeDaoMysql::getInstance();
        $this->currentTemplate = $currentTemplate;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/templates/template_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $id = $this->currentTemplate->getId();
        $data->assign("id", $id);

        $nameField = new TextField("template_{$id}_name_field", $this->getTextResource("template_editor_name_field"), $this->currentTemplate->getName(), true, false, null);
        $data->assign("name_field", $nameField->render());
        $filenameField = new TextField("template_{$id}_filename_field", $this->getTextResource("template_editor_filename_field"), $this->currentTemplate->getFileName(), false, false, null);
        $data->assign("filename_field", $filenameField->render());
        $data->assign("scopes_field", $this->renderScopesField());

        $varDefFields = array();
        foreach ($this->templateDao->getTemplateVarDefs($this->currentTemplate) as $templateVarDef) {
            $varDefId = $templateVarDef->getId();
            $varDefField = new TextField("var_def_{$varDefId}_default_value_field", $templateVarDef->getName(), $templateVarDef->getDefaultValue(), false, false, null);
            $varDefFields[] = $varDefField->render();
        }
        $data->assign("var_defs", $varDefFields);
    }

    private function renderScopesField(): string {
        $scopesIdentifierValuePair = array();
        foreach ($this->scopeDao->getScopes() as $scope) {
            $scopesIdentifierValuePair[] = array("name" => $this->getTextResource($scope->getIdentifier() . '_scope_label'), "value" => $scope->getId());
        }
        $currentScope = $this->templateVariant->getScope();
        $scopesField = new Pulldown("scope", "Scope", $currentScope->getId(), $scopesIdentifierValuePair, 200, true);
        return $scopesField->render();
    }

}