<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\frontend\handlers\FormStatus;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformField;
use const Pageflow\CMS_ROOT;

abstract class FormFieldVisual extends FormItemVisual {

    public function __construct(Page $page, ?Article $article, Webform $webform, WebformField $webformField) {
        parent::__construct($page, $article, $webform, $webformField);
    }

    public function getFormItemTemplateFilename(): string {
        return CMS_ROOT . "/frontend/templates/form-field.tpl";
    }

    public function loadFormItem(): void {
        $mandatory = false;
        if ($this->getFormItem() instanceof WebformField) {
            $mandatory = $this->getFormItem()->getMandatory();
        }
        $this->assign('value', FormStatus::getFieldValue($this->getFormItem()->getName()));
        $this->assign('mandatory', $mandatory);

        $this->loadFormField();
        $this->assign('form_field_html', $this->fetch($this->getFormFieldTemplateFilename()));
    }

    abstract function loadFormField(): void;

    abstract function getFormFieldTemplateFilename(): string;
}