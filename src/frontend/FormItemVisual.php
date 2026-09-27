<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\frontend\handlers\FormStatus;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformItem;
use const Pageflow\CMS_ROOT;

abstract class FormItemVisual extends FrontendVisual {

    private WebformItem $webformItem;
    private Webform $webform;

    public function __construct(Page $page, ?Article $article, Webform $webform, WebformItem $webformItem) {
        parent::__construct($page, $article);
        $this->webform = $webform;
        $this->webformItem = $webformItem;
    }

    public function getTemplateFilename(): string {
        return CMS_ROOT . "/frontend/templates/form-item.tpl";
    }

    public function loadVisual(?array &$data): void {
        $this->assign('label', $this->getFormItem()->getLabel());
        $this->assign('name', $this->getFormItem()->getName());
        $this->assign('value', FormStatus::getFieldValue($this->getFormItem()->getName()));
        $this->assign('has_error', FormStatus::getError($this->webform->getId(), $this->getFormItem()->getName()) != null);

        $this->loadFormItem();
        $this->assign('form_item_html', $this->fetch($this->getFormItemTemplateFilename()));
    }

    protected function getFormItem(): WebformItem {
        return $this->webformItem;
    }

    protected function getWebform(): Webform {
        return $this->webform;
    }

    abstract function loadFormItem(): void;

    abstract function getFormItemTemplateFilename(): string;

    public function getPresentable(): ?Presentable {
        return $this->webformItem;
    }
}