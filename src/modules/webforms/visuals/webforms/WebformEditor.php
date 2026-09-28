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
        $addTextFieldButton = new Button("", "webforms_add_textfield_button_label", "addFormField('textfield');");
        $data->assign("button_add_textfield", $addTextFieldButton->render());

        $addTextAreaButton = new Button("", "webforms_add_textarea_button_label", "addFormField('textarea');");
        $data->assign("button_add_textarea", $addTextAreaButton->render());

        $addButtonButton = new Button("", "webforms_add_button_button_label", "addFormField('button');");
        $data->assign("button_add_button", $addButtonButton->render());

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