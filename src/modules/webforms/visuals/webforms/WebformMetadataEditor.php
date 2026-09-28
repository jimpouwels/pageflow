<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms;

use Pageflow\Core\database\dao\ConfigDao;
use Pageflow\Core\database\dao\ConfigDaoMysql;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\SingleCheckbox;
use Pageflow\Core\view\views\TemplatePicker;
use Pageflow\Core\view\views\TextField;

class WebformMetadataEditor extends Panel {
    private WebForm $currentWebForm;
    private ConfigDao $configDao;

    public function __construct(WebForm $currentWebform) {
        parent::__construct('webforms_metdata_editor_panel_title', '');
        $this->currentWebForm = $currentWebform;
        $this->configDao = ConfigDaoMysql::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return 'webforms/templates/webforms/metadata_editor.tpl';
    }

    public function loadPanelContent(TemplateData $data): void {
        $titleTextField = new TextField("title", "webforms_editor_title_field", $this->currentWebForm->getTitle(), true, false, null);
        $data->assign("title_field", $titleTextField->render());

        $templatePickerField = new TemplatePicker("template", $this->getTextResource("webforms_editor_template_field"), $this->currentWebForm->getTemplateId(), false, "", $this->currentWebForm->getScope());
        $data->assign('template_picker', $templatePickerField->render());

        $captchaFieldKeyClass = "captcha_key_field_{$this->currentWebForm->getId()}";
        $data->assign('captcha_key_field_class', $captchaFieldKeyClass);
        $data->assign('include_captcha', $this->currentWebForm->getIncludeCaptcha());

        $captchaKeyField = new TextField('captcha_key', 'webforms_editor_captcha_key_field', $this->currentWebForm->getCaptchaKey(), true, true, null);
        $data->assign('captcha_key_field', $captchaKeyField->render());

        $captchaSecretField = new TextField('captcha_secret', 'webforms_editor_captcha_secret_field', $this->getCaptchaSecret(), true, true, null);
        $data->assign('captcha_secret_field', $captchaSecretField->render());

        $captchaCheckbox = new SingleCheckbox('include_captcha', 'webforms_editor_captcha_field', $this->currentWebForm->getIncludeCaptcha(), false, null);
        $captchaCheckbox->setOnChangeJS("onCaptchaChanged('{$captcha_key_field_class}')");
        $data->assign("include_captcha_field", $captchaCheckbox->render());
    }

    private function getCaptchaSecret(): ?string {
        return $this->configDao->getCaptchaSecret();
    }

}
