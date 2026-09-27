<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\TextArea;

class TemplateCodeEditor extends Panel {

    private Template $template;

    public function __construct(Template $template) {
        parent::__construct($this->getTextResource('templates.code_editor.panel_title'), 'template_content_panel');
        $this->template = $template;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/templates/template_code_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $textArea = new TextArea("template_{$this->template->getId()}_code_field", $this->getTextResource('template.code_editor.label'), $this->template->getCode(), true, false, 'code_editor');
        $data->assign('code_editor', $textArea->render());
    }

}
