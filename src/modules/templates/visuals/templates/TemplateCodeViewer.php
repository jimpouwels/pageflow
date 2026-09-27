<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class TemplateCodeViewer extends Panel {

    private Template $template;

    public function __construct(Template $template) {
        parent::__construct($this->getTextResource('template.code_viewer.panel_title'), 'template_content_panel');
        $this->template = $template;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/templates/template_code_viewer.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign('file_content', $this->getTemplateCode());
    }

    private function getTemplateCode(): ?string {
        return htmlspecialchars($this->template->getTemplateFileCode());
    }

}
