<?php

namespace Pageflow\Core\modules\webforms\model;

use Pageflow\Core\modules\templates\model\Presentable;

class Webform extends Presentable {

    public static int $SCOPE = 19;
    private string $title;
    private array $formFields;
    private bool $includeCaptcha;
    private ?string $captchaKey;

    public function __construct() {
        parent::__construct(self::$SCOPE);
    }

    public static function constructFromRecord(array $record, array $formFields): Webform {
        $form = new Webform();
        $form->setFormFields($formFields);
        $form->initFromDb($record);
        return $form;
    }

    protected function initFromDb(array $row): void {
        $this->setTitle($row['title']);
        $this->setIncludeCaptcha($row['include_captcha'] == 1);
        $this->setCaptchaKey($row['captcha_key']);
        parent::initFromDb($row);
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getFormFields(): array {
        usort($this->formFields, function (WebFormItem $f1, WebFormItem $f2) {
            return $f1->getOrderNr() - $f2->getOrderNr();
        });
        return $this->formFields;
    }

    public function setFormFields(array $formFields): void {
        $this->formFields = $formFields;
    }

    public function deleteWebFormItem(int $formItemId): void {
        $this->formFields = array_filter($this->formFields, function ($item) use ($formItemId) {
            return $item->getId() !== $formItemId;
        });
    }

    public function getIncludeCaptcha(): bool {
        return $this->includeCaptcha;
    }

    public function setIncludeCaptcha(bool $includeCaptcha): void {
        $this->includeCaptcha = $includeCaptcha;
    }

    public function getCaptchaKey(): ?string {
        return $this->captchaKey;
    }

    public function setCaptchaKey(?string $captchaKey): void {
        $this->captchaKey = $captchaKey;
    }

}