<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\frontend\handlers\FormStatus;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\WebformItemFactory;
use const Pageflow\CMS_ROOT;

class FormFrontendVisual extends FrontendVisual {

    private WebformItemFactory $webformItemFactory;

    private Webform $webform;

    public function __construct(Page $page, ?Article $article, Webform $webform) {
        parent::__construct($page, $article);
        $this->webform = $webform;
        $this->webformItemFactory = WebformItemFactory::getInstance();
    }

    public function getTemplateFilename(): string {
        return CMS_ROOT . "/frontend/templates/sa-form.tpl";
    }

    public function loadVisual(?array &$data): void {
        $this->assign('webform_id', $this->webform->getId());
        if ($this->webform->getIncludeCaptcha()) {
            $captchaKey = $this->webform->getCaptchaKey();
            $this->assign('captcha_key', $captchaKey);
        }
        $this->assign('title', $this->webform->getTitle());

        $webformChildData = $this->createChildData();
        $webformData = $this->renderWebForm($this->webform);
        $webformChildData->assign('webform', $webformData);

        // submit status
        $webformChildData->assign('has_captcha_error', null);
        $webformChildData->assign('is_submitted', false);
        if (FormStatus::isSubmitted($this->webform->getId())) {
            $webformChildData->assign('is_submitted', true);
            $webformChildData->assign('has_captcha_error', FormStatus::getError($this->webform->getId(), "captcha"));
        }
        $this->assign('form_html', $this->getTemplateEngine()->fetch($this->getFrontendTemplateDir() . "/" . $this->getTemplateService()->getTemplateForPresentable($this->webform)->getFileName(), $webformChildData));
        FormStatus::clearErrors($this->webform->getId());
    }

    public function getPresentable(): ?Presentable {
        return $this->webform;
    }

    private function renderWebForm(Webform $webform): array {
        $webformData = array();
        $webformData['title'] = $webform->getTitle();
        $webformData['fields'] = $this->renderFields($webform);
        return $webformData;
    }

    private function renderFields(Webform $webform): array {
        $fields = array();
        foreach ($webform->getFormFields() as $formField) {
            $field = $this->webformItemFactory->getFrontendVisualFor($webform, $formField, $this->getPage(), $this->getArticle());
            $fields[] = $field->render();
        }
        return $fields;
    }
}