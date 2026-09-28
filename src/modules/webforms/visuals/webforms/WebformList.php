<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms;

use Pageflow\Core\database\dao\WebformDao;
use Pageflow\Core\database\dao\WebformDaoMysql;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\WebformRequestHandler;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class WebformList extends Panel {

    private ?WebForm $currentWebform;
    private WebformDao $webformDao;

    public function __construct(?WebForm $currentWebform) {
        parent::__construct("webforms_list_panel_title", 'webforms_list');
        $this->currentWebform = $currentWebform;
        $this->webformDao = WebformDaoMysql::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "webforms/templates/webforms/list.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $webforms = $this->webformDao->getAllWebForms();
        $webforms_data = array();
        foreach ($webforms as $webform) {
            $webform_data = array();
            $webform_data["id"] = $webform->getId();
            $webform_data["title"] = $webform->getTitle();
            $is_selected = false;
            if ($this->currentWebform) {
                $is_selected = $webform->getId() == $this->currentWebform->getId();
            }
            $webform_data["is_selected"] = $is_selected;
            $webforms_data[] = $webform_data;
        }
        $data->assign("webforms", $webforms_data);
    }

}
