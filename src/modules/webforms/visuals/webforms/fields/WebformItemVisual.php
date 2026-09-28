<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms\fields;

use Pageflow\Core\modules\webforms\model\WebformItem;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\TemplatePicker;
use Pageflow\Core\view\views\TextField;
use Pageflow\Core\view\views\Visual;

abstract class WebformItemVisual extends Visual {

    private WebformItem $webformItem;

    public function __construct(WebformItem $webformItem) {
        parent::__construct();
        $this->webformItem = $webformItem;
    }

    public function getTemplateFilename(): string {
        return "webforms/templates/webforms/fields/webform_item.tpl";
    }

    protected function getWebFormItem(): WebformItem {
        return $this->webformItem;
    }

    abstract function getFormItemTemplate(): string;

    abstract function loadItemContent(TemplateData $data): void;

    public function load(): void {
        $formItemContentTemplateData = $this->createChildData();
        $this->loadItemContent($formItemContentTemplateData);

        $templatePicker = new TemplatePicker("webform_item_{$this->webformItem->getId()}_template", "", $this->webformItem->getTemplateId(), false, "template_picker", $this->webformItem->getScope());
        $this->assign('template_picker', $templatePicker->render());

        $this->assign('id', $this->webformItem->getId());
        $this->assign('type', $this->webformItem->getType());
        $this->assign('index', 0);
        $nameField = new TextField("webform_item_{$this->webformItem->getId()}_name", "webforms_editor_field_name_label", $this->webformItem->getName(), true, false, null);
        $labelField = new TextField("webform_item_{$this->webformItem->getId()}_label", "webforms_editor_field_label_label", $this->webformItem->getLabel(), true, false, null);
        $this->assign("name_field", $nameField->render());
        $this->assign("label_field", $labelField->render());
        $this->assign('item_editor', $this->getTemplateEngine()->fetch($this->getFormItemTemplate(), $formItemContentTemplateData));
    }

}
