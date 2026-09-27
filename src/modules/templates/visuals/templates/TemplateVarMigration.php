<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\TemplateRequestHandler;
use Pageflow\Core\utilities\Arrays;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\TextField;

class TemplateVarMigration extends Panel {
    private TemplateDao $templateDao;
    private TemplateRequestHandler $requestHandler;

    public function __construct(TemplateRequestHandler $requestHandler) {
        parent::__construct('template_var_migration_panel_title', 'template_var_migration_panel');
        $this->templateDao = TemplateDaoMysql::getInstance();
        $this->requestHandler = $requestHandler;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/templates/template_var_migration.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $currentTemplate = $this->requestHandler->getCurrentTemplate();
        $templatesForFile = array();
        foreach ($this->templateDao->getTemplateVariantsForTemplate($currentTemplate) as $templateVariant) {
            $templateVarsForFile = array();
            $templateVarsForFile['name'] = $templateVariant->getName();

            $existingTemplateVars = array();
            foreach ($this->templateDao->getTemplateVars($templateVariant) as $templateVar) {
                $existingTemplateVar = array();
                $existingTemplateVar['name'] = $templateVar->getName();
                $existingTemplateVar['value'] = $templateVar->getValue();

                $existingTemplateVar['deleted'] = false;
                if (!Arrays::firstMatch($this->requestHandler->getParsedVarDefs(), fn($parsedVar) => $templateVar->getName() == $parsedVar)) {
                    $existingTemplateVar['deleted'] = true;
                }

                $existingTemplateVars[] = $existingTemplateVar;
            }
            $templateVarsForFile['vars'] = $existingTemplateVars;

            $newVarsField = array();
            foreach ($this->requestHandler->getParsedVarDefs() as $parsedVar) {
                if (!Arrays::firstMatch($templateVariant->getTemplateVars(), fn($template_var) => $template_var->getName() == $parsedVar)) {
                    $templateId = $templateVariant->getId();
                    $newVarTextfield = new TextField("new_var_$templateId|{$parsedVar}", $parsedVar, "", false, false, null);
                    $newVarsField[] = $newVarTextfield->render();
                }
            }
            $templateVarsForFile['new_vars'] = $newVarsField;
            $templatesForFile[] = $templateVarsForFile;

        }
        $data->assign("templates", $templatesForFile);
    }

}