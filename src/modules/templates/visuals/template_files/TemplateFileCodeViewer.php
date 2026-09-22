<?php

namespace Pageflow\Core\modules\templates\visuals\template_files;

use Pageflow\Core\modules\templates\model\TemplateFile;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class TemplateFileCodeViewer extends Panel {

    private TemplateFile $templateFile;

    public function __construct(TemplateFile $templateFile) {
        parent::__construct($this->getTextResource('template_files.code_viewer.panel_title'), 'template_content_panel');
        $this->templateFile = $templateFile;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/template_code_viewer.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign('file_content', $this->getTemplateCode());
    }

    private function getTemplateCode(): ?string {
        return htmlspecialchars($this->templateFile->getTemplateFileCode());
    }

}
