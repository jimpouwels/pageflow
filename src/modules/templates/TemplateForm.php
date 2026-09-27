<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Template;

class TemplateForm extends Form {

    private Template $template;
    private TemplateDao $templateDao;
    private array $parseVarDefs = array();
    private bool $reloading = false;

    public function __construct(?Template $template) {
        $this->template = $template;
        $this->templateDao = TemplateDaoMysql::getInstance();
    }

    public function getParseVarDefs(): array {
        return $this->parseVarDefs;
    }

    public function setReloading(): void {
        $this->reloading = true;
    }

    public function loadFields(): void {
        $id = $this->template->getId();
        $this->template->setName($this->getMandatoryFieldValue("template_{$id}_name_field"));
        $this->template->setFileName($this->getFieldValue("template_{$id}_filename_field"));
        $this->template->setCode($this->getFieldValue("template_{$id}_code_field"));

        $this->parseVarDefs = $this->parseVarDefs();

        foreach ($this->template->getTemplateVarDefs() as $varDef) {
            $varDefId = $varDef->getId();
            $varDef->setDefaultValue($this->getFieldValue("var_def_{$varDefId}_default_value_field"));
            $this->templateDao->updateTemplateVarDef($varDef);
        }

        // update template file
        foreach ($this->parseVarDefs as $parsedVarDef) {
            if (!array_filter($this->template->getTemplateVarDefs(), fn($varDef) => $varDef->getName() == $parsedVarDef)) {
                $template_var_def = $this->templateDao->storeTemplateVarDef($this->template, $parsedVarDef);
                $this->template->addTemplateVarDef($template_var_def);
            }
        }
        foreach ($this->template->getTemplateVarDefs() as $templateFileVarDef) {
            if (!array_filter($this->parseVarDefs, fn($parsedVarDef) => $templateFileVarDef->getName() == $parsedVarDef)) {
                $this->templateDao->deleteTemplateVarDef($templateFileVarDef);
                $this->template->deleteTemplateVarDef($templateFileVarDef);
            }
        }

        // update all templates (migration)
        if (!$this->reloading) {
            foreach ($this->templateDao->getTemplateVariantsForTemplate($this->template) as $templateVariant) {
                foreach ($templateVariant->getTemplateVars() as $templateVar) {
                    if (!array_filter($this->parseVarDefs, fn($parsedVarDef) => $templateVar->getName() == $parsedVarDef)) {
                        $this->templateDao->deleteTemplateVar($templateVar);
                        $templateVariant->deleteTemplateVar($templateVar);
                    }
                }
                foreach ($this->parseVarDefs as $parseVarDef) {
                    foreach ($this->templateDao->getTemplateVariantsForTemplate($this->template) as $templateVariant) {
                        if (!array_filter($templateVariant->getTemplateVars(), fn($templateVar) => $templateVar->getName() == $parseVarDef)) {
                            $templateId = $templateVariant->getId();
                            $this->templateDao->storeTemplateVar($templateVariant, $parseVarDef, $this->getFieldValue("new_var_$templateId|{$parseVarDef}"));
                        }
                    }
                }
            }
        }

    }

    private function parseVarDefs(): array {
        $parsedVarDefs = array();
        $code = $this->template->getTemplateFileCode();
        $matches = null;
        preg_match_all('/\$var\.(.*?)[\ })|]/', $code, $matches);

        for ($i = 0; $i < count($matches[1]); $i++) {
            $varDefName = $matches[1][$i];
            $parsedVarDefs[] = $varDefName;
        }
        return array_unique($parsedVarDefs);
    }

}
    