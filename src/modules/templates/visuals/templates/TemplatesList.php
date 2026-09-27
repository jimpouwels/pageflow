<?php

namespace Pageflow\Core\modules\templates\visuals\templates;

use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class TemplatesList extends Panel {
    private TemplateDao $templateDao;
    private ?Template $currentTemplate;

    public function __construct(?Template $currentTemplate = null) {
        parent::__construct('templates_list_title', 'template_list_panel');
        $this->templateDao = TemplateDaoMysql::getInstance();
        $this->currentTemplate = $currentTemplate;
    }

    public function getPanelContentTemplate(): string {
        return "templates/templates/templates/template_list.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign("templates", $this->getAllTemplateFiles());
    }

    private function getAllTemplateFiles(): array {
        $templates = array();
        foreach ($this->templateDao->getTemplates() as $template) {
            $templateArray = array();
            $templateArray["id"] = $template->getId();
            $templateArray["name"] = $template->getName();
            $templateArray["is_active"] = $this->currentTemplate && $this->currentTemplate->getId() === $template->getId();
            $templates[] = $templateArray;
        }
        return $templates;
    }

}