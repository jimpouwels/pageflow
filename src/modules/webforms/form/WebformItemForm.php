<?php

namespace Pageflow\Core\modules\webforms\form;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\modules\webforms\model\WebformItem;

abstract class WebformItemForm extends Form {

    private WebformItem $webformItem;

    public function __construct(WebformItem $webformButton) {
        $this->webformItem = $webformButton;
    }

    public function loadFields(): void {
        $this->webformItem->setLabel($this->getMandatoryFieldValue("webform_item_{$this->webformItem->getId()}_label"));
        $this->webformItem->setName($this->getMandatoryFieldValue("webform_item_{$this->webformItem->getId()}_name"));

        $templateIdStringValue = $this->getFieldValue("webform_item_{$this->webformItem->getId()}_template");
        $templateId = null;
        if (!empty($templateIdStringValue)) {
            $templateId = intval($templateIdStringValue);
        }
        $this->webformItem->setTemplateId($templateId);

        $this->loadItemFields();
    }

    public abstract function loadItemFields(): void;

    protected function getWebFormItem(): WebformItem {
        return $this->webformItem;
    }
}
