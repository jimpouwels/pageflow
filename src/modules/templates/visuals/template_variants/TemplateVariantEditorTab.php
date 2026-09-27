<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\view\views\Visual;

class TemplateVariantEditorTab extends Visual {

    private ?TemplateVariant $currentTemplateVariant;
    private ?Scope $currentScope;

    public function __construct(?TemplateVariant $currentTemplateVariant, ?Scope $currentScope) {
        parent::__construct();
        $this->currentTemplateVariant = $currentTemplateVariant;
        $this->currentScope = $currentScope;
    }

    public function getTemplateFilename(): string {
        return "templates/templates/template_variants/template_variant_editor_tab.tpl";
    }

    public function load(): void {
        $this->assign("current_template_variant_id", $this->getCurrentTemplateId());
        if (!is_null($this->currentTemplateVariant)) {
            $this->assign("template_variant_editor", $this->renderTemplateEditor());
            $this->assign("template_variant_var_editor", $this->renderTemplateVarEditor());
            if (!is_null($this->currentScope)) {
                $this->assign("back_url", $this->getBackendBaseUrl() . "&scope=" . $this->currentScope->getIdentifier());
            }
        }
        $this->assign("scope_selector", $this->getScopeSelector());
        if (!is_null($this->currentScope)) {
            $this->assign("template_variant_list", $this->renderTemplateVariantsList());
        }
    }

    private function getScopeSelector(): string {
        return (new ScopeSelector($this->currentScope))->render();
    }

    private function renderTemplateEditor(): string {
        return (new TemplateVariantEditor($this->currentTemplateVariant))->render();
    }

    private function renderTemplateVarEditor(): string {
        return (new TemplateVarEditor($this->currentTemplateVariant))->render();
    }

    private function renderTemplateVariantsList(): string {
        return (new TemplateVariantsList($this->currentScope))->render();
    }

    private function getCurrentTemplateId(): ?int {
        return $this->currentTemplateVariant?->getId();
    }

}