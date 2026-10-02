<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\view\views\Visual;
use Pageflow\Core\database\dao\ScopeDao;
use Pageflow\Core\database\dao\ScopeDaoMysql;

class TemplateVariantEditorTab extends Visual {

    private ?TemplateVariant $currentTemplateVariant;
    private ?Scope $currentScope;
    private ScopeDao $scopeDao;
    private bool $showUnassigned;

    public function __construct(?TemplateVariant $currentTemplateVariant, ?Scope $currentScope, bool $showUnassigned = false) {
        parent::__construct();
        $this->currentTemplateVariant = $currentTemplateVariant;
        $this->currentScope = $currentScope;
        $this->scopeDao = ScopeDaoMysql::getInstance();
        $this->showUnassigned = $showUnassigned;
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
        if ($this->showUnassigned) {
            $this->assign("template_variant_list", $this->renderTemplateVariantsList(null));
        } else if (!is_null($this->currentScope)) {
            $this->assign("template_variant_list", $this->renderTemplateVariantsList($this->currentScope));
        }
    }

    private function getScopeSelector(): string {
        $currentScope = $this->currentScope;
        if (!$currentScope) {
            if ($this->currentTemplateVariant) {
                $template = $this->getTemplateService()->getTemplateForTemplateVariant($this->currentTemplateVariant);
                if ($template) {
                    $currentScope = $this->scopeDao->getScope($template->getScopeId());
                }
            }
        }
        return (new ScopeSelector($currentScope, $this->showUnassigned))->render();
    }

    private function renderTemplateEditor(): string {
        return (new TemplateVariantEditor($this->currentTemplateVariant))->render();
    }

    private function renderTemplateVarEditor(): string {
        return (new TemplateVarEditor($this->currentTemplateVariant))->render();
    }

    private function renderTemplateVariantsList(?Scope $scope): string {
        return (new TemplateVariantsList($scope))->render();
    }

    private function getCurrentTemplateId(): ?int {
        return $this->currentTemplateVariant?->getId();
    }

}