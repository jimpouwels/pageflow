<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms\fields;

use Pageflow\Core\modules\webforms\model\WebformItem;
use Pageflow\Core\view\TemplateData;

class WebformButtonVisual extends WebformItemVisual {

    public function __construct(WebformItem $webformItem) {
        parent::__construct($webformItem);
    }

    public function getFormItemTemplate(): string {
        return "webforms/templates/webforms/fields/webform_button.tpl";
    }

    public function loadItemContent(TemplateData $data): void {}
}