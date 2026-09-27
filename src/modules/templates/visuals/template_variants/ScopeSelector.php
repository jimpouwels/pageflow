<?php

namespace Pageflow\Core\modules\templates\visuals\template_variants;

use Pageflow\Core\database\dao\ScopeDao;
use Pageflow\Core\database\dao\ScopeDaoMysql;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class ScopeSelector extends Panel {

    private ScopeDao $scopeDao;
    private ?Scope $currentScope;

    public function __construct(?Scope $currentScope = null) {
        parent::__construct('templates_scope_list_title', 'scope_selector_panel');
        $this->scopeDao = ScopeDaoMysql::getInstance();
        $this->currentScope = $currentScope;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_variants/scope_selector.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign("scopes", $this->getAllScopes());
    }

    private function getAllScopes(): array {
        $scopes = array();
        foreach ($this->scopeDao->getScopes() as $scope) {
            $scopeArray = array();
            $scopeArray["label"] = $this->getTextResource($scope->getIdentifier() . "_scope_label");
            $scopeArray["identifier"] = $scope->getIdentifier();
            $scopeArray["is_active"] = $this->currentScope && $this->currentScope->getIdentifier() === $scope->getIdentifier();
            $scopes[] = $scopeArray;
        }
        return $scopes;
    }

}
