<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms;

use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\WebformItemFactory;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Button;
use Pageflow\Core\view\views\Panel;

class WebformEditor extends Panel {

    private WebForm $currentWebform;
    private WebformItemFactory $webformItemFactory;

    public function __construct(?WebForm $currentWebform) {
        parent::__construct("webforms_webform_editor_panel_title");
        $this->webformItemFactory = WebformItemFactory::getInstance();
        $this->currentWebform = $currentWebform;
    }

    public function getPanelContentTemplate(): string {
        return 'webforms/templates/webforms/webform_editor.tpl';
    }

    public function loadPanelContent(TemplateData $data): void {
        $add_textfield_button = new Button("", "webforms_add_textfield_button_label", "addFormField('textfield');");
        $data->assign("button_add_textfield", $add_textfield_button->render());

        $add_textarea_button = new Button("", "webforms_add_textarea_button_label", "addFormField('textarea');");
        $data->assign("button_add_textarea", $add_textarea_button->render());

        $add_button_button = new Button("", "webforms_add_button_button_label", "addFormField('button');");
        $data->assign("button_add_button", $add_button_button->render());

        $data->assign("form_fields", $this->renderFormFields());
    }

    private function renderFormFields(): array {
        $formFieldsData = array();
        foreach ($this->currentWebform->getFormFields() as $formField) {
            $formFieldData = $this->webformItemFactory->getBackendVisualFor($formField);
            $formFieldsData[] = $formFieldData->render();
        }
        return $formFieldsData;
    }

}

?>