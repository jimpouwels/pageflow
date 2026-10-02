<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\database\dao\ScopeDao;
use Pageflow\Core\database\dao\ScopeDaoMysql;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\Pulldown;
use Pageflow\Core\view\views\TextField;
use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;

class TemplateVariantEditor extends Panel {

    private TemplateVariant $templateVariant;
    private ScopeDao $scopeDao;
    private TemplateService $templateService;

    public function __construct(TemplateVariant $templateVariant) {
        parent::__construct('Template variant bewerken', 'template_variant_editor_panel');
        $this->templateVariant = $templateVariant;
        $this->scopeDao = ScopeDaoMysql::getInstance();
        $this->templateService = TemplateInteractor::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_variants/template_variant_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign("template_variant_id", $this->templateVariant->getId());
        $this->assignEditFields($data);
    }

    private function assignEditFields(TemplateData $data): void {
        $name_field = new TextField("name", "template_variant_editor_name_field", $this->templateVariant->getName(), true, false, null);
        $data->assign("name_field", $name_field->render());

        $templateFileSelect = new Pulldown("template_variant_editor_template", $this->getTextResource('template_variant_editor_template_field'), strval($this->templateVariant->getTemplateId()), $this->getTemplateFilesData(), false, null, true);
        $data->assign("template_selector", $templateFileSelect->render());
    }

    private function getTemplateFilesData(): array {
        $templateFilesData = array();
        foreach ($this->templateService->getTemplates() as $templateFile) {
            $templateFileData = array();
            $templateFileData['name'] = $templateFile->getName();
            $templateFileData['value'] = $templateFile->getId();
            $templateFilesData[] = $templateFileData;
        }
        return $templateFilesData;
    }

}