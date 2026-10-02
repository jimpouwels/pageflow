<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\core\form\FormException;
use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\utilities\Arrays;

class TemplateVariantEditorForm extends Form {
    private TemplateVariant $templateVariant;
    private TemplateDao $templateDao;

    public function __construct(TemplateVariant $templateVariant) {
        $this->templateVariant = $templateVariant;
        $this->templateDao = TemplateDaoMysql::getInstance();
    }

    public function loadFields(): void {
        $this->templateVariant->setName($this->getMandatoryFieldValue("name"));

        $newTemplateId = $this->getFieldValue("template_variant_editor_template");
        if ($newTemplateId != $this->templateVariant->getTemplateId()) {
            foreach ($this->templateVariant->getTemplateVars() as $templateVar) {
                $this->templateDao->deleteTemplateVar($templateVar);
            }
            $this->templateVariant->setTemplateVars([]);
        }

        if ($newTemplateId) {
            $this->templateVariant->setTemplateId(intval($newTemplateId));

            foreach ($this->templateDao->getTemplate($this->templateVariant->getTemplateId())->getTemplateVarDefs() as $varDef) {
                if (!Arrays::firstMatch($this->templateVariant->getTemplateVars(), function ($tv) use ($varDef) {
                    return $varDef->getName() == $tv->getName();
                })) {
                    $templateVar = $this->templateDao->storeTemplateVar($this->templateVariant, $varDef->getName());
                    $this->templateVariant->addTemplateVar($templateVar);
                }
            }
        }

        if ($this->hasErrors()) {
            throw new FormException();
        }
        foreach ($this->templateVariant->getTemplateVars() as $templateVar) {
            $templateVarId = $templateVar->getId();
            $templateVar->setValue($this->getFieldValue("template_var_{$templateVarId}_field"));
            $this->templateDao->updateTemplateVar($templateVar);
        }
    }

}
    