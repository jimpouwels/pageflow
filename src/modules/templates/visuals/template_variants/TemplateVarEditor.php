<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\TextField;

class TemplateVarEditor extends Panel {

    private TemplateVariant $template;
    private TemplateService $templateService;

    public function __construct(TemplateVariant $template) {
        parent::__construct($this->getTextResource('template_var_editor_panel_title'), 'template_editor_panel');
        $this->template = $template;
        $this->templateService = TemplateInteractor::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_variants/template_variant_var_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $varFields = array();
        foreach ($this->template->getTemplateVars() as $templateVar) {
            $templateVarId = $templateVar->getId();

            $defaultValue = $this->templateService->getTemplateVarDefByTemplateVar($this->template, $templateVar)->getDefaultValue();
            $postfix = "<strong>Default:</strong> $defaultValue";
            $varField = new TextField("template_var_{$templateVarId}_field", $templateVar->getName(), $templateVar->getValue(), false, false, null, true, $postfix);
            $varFields[] = $varField->render();
        }
        $data->assign("var_fields", $varFields);

    }
}
