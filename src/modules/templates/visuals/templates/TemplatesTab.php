<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\TemplateRequestHandler;
use Pageflow\Core\view\views\Visual;

class TemplatesTab extends Visual {

    private ?Template $currentTemplate;
    private TemplateRequestHandler $requestHandler;

    public function __construct(TemplateRequestHandler $requestHandler) {
        parent::__construct();
        $this->currentTemplate = $requestHandler->getCurrentTemplate();
        $this->requestHandler = $requestHandler;
    }

    public function getTemplateFilename(): string {
        return "templates/templates/templates/template_tab.tpl";
    }

    public function load(): void {
        $this->assign("current_template_id", $this->getCurrentTemplateId());
        $templatesList = new TemplatesList($this->currentTemplate);
        $this->assign("templates_list", $templatesList->render());

        $editorHtml = "";
        if ($this->currentTemplate) {
            $templateEditor = new TemplateEditor($this->currentTemplate);
            $editorHtml = $templateEditor->render();
        }
        $this->assign("template_editor", $editorHtml);

        $varMigrationHtml = "";
        if (count($this->requestHandler->getParsedVarDefs()) > 0) {
            $templateVarMigration = new TemplateVarMigration($this->requestHandler);
            $varMigrationHtml = $templateVarMigration->render();
        }
        $this->assign("template_var_migration", $varMigrationHtml);

        $codeViewerHtml = "";
        $codeEditorHtml = "";
        if ($this->currentTemplate) {
            $codeViewerHtml = $this->renderTemplateCodeViewer();
            $codeEditorHtml = $this->renderTemplateCodeEditor();
        }
        
        $this->assign("template_code_editor", $codeEditorHtml);
        $this->assign("template_code_viewer", $codeViewerHtml);
    }

    private function getCurrentTemplateId(): string {
        $id = "";
        if ($this->currentTemplate) {
            $id = $this->currentTemplate->getId();
        }
        return $id;
    }

    private function renderTemplateCodeViewer(): string {
        return (new TemplateCodeViewer($this->currentTemplate))->render();
    }

    private function renderTemplateCodeEditor(): string {
        return (new TemplateCodeEditor($this->currentTemplate))->render();
    }

}