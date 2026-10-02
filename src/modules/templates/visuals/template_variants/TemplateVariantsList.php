<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\InformationMessage;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\SingleCheckbox;

class TemplateVariantsList extends Panel {

    private ?Scope $scope;

    public function __construct(?Scope $scope) {
        $title = $scope ? $this->getTextResource($scope->getIdentifier() . '_scope_label') . ' templates' : 'Niet-toegewezen templates';
        parent::__construct($title, 'template_list_panel');
        $this->scope = $scope;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_variants/template_variant_list.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        if ($this->scope) {
            $data->assign("scope", $this->scope->getIdentifier());
        }
        $data->assign("template_variants", $this->getTemplateVariantsData());
        $data->assign("information_message", $this->renderInformationMessage());
    }

    private function getTemplateVariantsData(): array {
        return $this->scope ? $this->getTemplateVariantsForScope($this->scope) : $this->getUnassignedTemplateVariants();
    }

    private function getTemplateVariantsForScope(Scope $scope): array {
        $templatesData = array();
        $templates = $this->getTemplateService()->getTemplatesByScope($scope);

        foreach ($templates as $template) {
            foreach ($this->getTemplateService()->getTemplateVariantsForTemplate($template) as $templateVariant) {
                $templatesData[] = $this->toTemplateData($templateVariant);
            }
        }
        return $templatesData;
    }

    private function getUnassignedTemplateVariants(): array {
        $templatesData = array();
        foreach ($this->getTemplateService()->getUnassignedTemplateVariants() as $templateVariant) {
            $templatesData[] = $this->toTemplateData($templateVariant);
        }
        return $templatesData;
    }

    private function toTemplateData(TemplateVariant $templateVariant): array {
        $templateData = array();
        $templateData["id"] = $templateVariant->getId();
        $templateData["name"] = $templateVariant->getName();
        $templateData["delete_checkbox"] = $this->renderDeleteCheckBox($templateVariant);
        return $templateData;
    }

    private function renderDeleteCheckBox(TemplateVariant $templateVariant): string {
        $checkbox = new SingleCheckbox("template_variant_" . $templateVariant->getId() . "_delete", "", "", false, "");
        return $checkbox->render();
    }

    private function renderInformationMessage(): string {
        $informationMessage = new InformationMessage("Geen templates gevonden. Klik op 'toevoegen' om een nieuw template te maken.");
        return $informationMessage->render();
    }

}
