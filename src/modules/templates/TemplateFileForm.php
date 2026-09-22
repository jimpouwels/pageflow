<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\database\dao\TemplateDao;
use Pageflow\Core\database\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\TemplateFile;

class TemplateFileForm extends Form {

    private TemplateFile $templateFile;
    private TemplateDao $templateDao;
    private array $parseVarDefs = array();
    private bool $reloading = false;

    public function __construct(TemplateFile $templateFile) {
        $this->templateFile = $templateFile;
        $this->templateDao = TemplateDaoMysql::getInstance();
    }

    public function getParseVarDefs(): array {
        return $this->parseVarDefs;
    }

    public function setReloading(): void {
        $this->reloading = true;
    }

    public function loadFields(): void {
        $id = $this->templateFile->getId();
        $this->templateFile->setName($this->getMandatoryFieldValue("template_file_{$id}_name_field"));
        $this->templateFile->setFileName($this->getFieldValue("template_file_{$id}_filename_field"));
        $this->templateFile->setCode($this->getFieldValue("template_file_{$id}_code_field"));

        $this->parseVarDefs = $this->parseVarDefs();

        foreach ($this->templateFile->getTemplateVarDefs() as $varDef) {
            $varDefId = $varDef->getId();
            $varDef->setDefaultValue($this->getFieldValue("var_def_{$varDefId}_default_value_field"));
            $this->templateDao->updateTemplateVarDef($varDef);
        }

        // update template file
        foreach ($this->parseVarDefs as $parsedVarDef) {
            if (!array_filter($this->templateFile->getTemplateVarDefs(), fn($varDef) => $varDef->getName() == $parsedVarDef)) {
                $template_var_def = $this->templateDao->storeTemplateVarDef($this->templateFile, $parsedVarDef);
                $this->templateFile->addTemplateVarDef($template_var_def);
            }
        }
        foreach ($this->templateFile->getTemplateVarDefs() as $templateFileVarDef) {
            if (!array_filter($this->parseVarDefs, fn($parsedVarDef) => $templateFileVarDef->getName() == $parsedVarDef)) {
                $this->templateDao->deleteTemplateVarDef($templateFileVarDef);
                $this->templateFile->deleteTemplateVarDef($templateFileVarDef);
            }
        }

        // update all templates (migration)
        if (!$this->reloading) {
            foreach ($this->templateDao->getTemplatesForTemplateFile($this->templateFile) as $template) {
                foreach ($template->getTemplateVars() as $templateVar) {
                    if (!array_filter($this->parseVarDefs, fn($parsedVarDef) => $templateVar->getName() == $parsedVarDef)) {
                        $this->templateDao->deleteTemplateVar($templateVar);
                        $template->deleteTemplateVar($templateVar);
                    }
                }
                foreach ($this->parseVarDefs as $parseVarDef) {
                    foreach ($this->templateDao->getTemplatesForTemplateFile($this->templateFile) as $template) {
                        if (!array_filter($template->getTemplateVars(), fn($templateVar) => $templateVar->getName() == $parseVarDef)) {
                            $templateId = $template->getId();
                            $this->templateDao->storeTemplateVar($template, $parseVarDef, $this->getFieldValue("new_var_$templateId|{$parseVarDef}"));
                        }
                    }
                }
            }
        }

    }

    private function parseVarDefs(): array {
        $parsedVarDefs = array();
        $code = $this->templateFile->getTemplateFileCode();
        $matches = null;
        preg_match_all('/\$var\.(.*?)[\ })|]/', $code, $matches);

        for ($i = 0; $i < count($matches[1]); $i++) {
            $varDefName = $matches[1][$i];
            $parsedVarDefs[] = $varDefName;
        }
        return array_unique($parsedVarDefs);
    }

}
    