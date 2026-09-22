<?php

namespace Pageflow\Core\modules\templates\visuals\template_files;

use Pageflow\Core\modules\templates\model\TemplateFile;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\TextArea;

class TemplateFileCodeEditor extends Panel {

    private TemplateFile $templateFile;

    public function __construct(TemplateFile $templateFile) {
        parent::__construct($this->getTextResource('template_files.code_editor.panel_title'), 'template_content_panel');
        $this->templateFile = $templateFile;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_code_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $textArea = new TextArea("template_file_{$this->templateFile->getId()}_code_field", $this->getTextResource('template_files.code_editor.label'), $this->templateFile->getCode(), true, false, 'code_editor');
        $data->assign('code_editor', $textArea->render());
    }

}
