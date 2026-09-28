<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms\fields;

use Pageflow\Core\modules\webforms\model\WebformItem;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\SingleCheckbox;

abstract class WebformFieldVisual extends WebformItemVisual {

    public function __construct(WebformItem $webformItem) {
        parent::__construct($webformItem);
    }

    public function getFormItemTemplate(): string {
        return "webforms/templates/webforms/fields/webform_field.tpl";
    }

    abstract function getFormFieldTemplate(): string;

    abstract function loadFieldContent(TemplateData $data): void;

    public function loadItemContent(TemplateData $data): void {
        $formFieldContentTemplateData = $this->createChildData();
        $this->loadFieldContent($formFieldContentTemplateData);
        $data->assign('field_editor', $this->getTemplateEngine()->fetch($this->getFormFieldTemplate(), $formFieldContentTemplateData));

        $mandatoryField = new SingleCheckbox("webform_field_{$this->getWebFormItem()->getId()}_mandatory", 'webforms_editor_field_mandatory_label', $this->getWebFormItem()->getMandatory(), false, null);
        $data->assign('mandatory_field', $mandatoryField->render());
    }

}