<?php

namespace Pageflow\Core\modules\webforms;

use Pageflow\Core\core\form\FormException;
use Pageflow\Core\database\dao\ConfigDao;
use Pageflow\Core\database\dao\ConfigDaoMysql;
use Pageflow\Core\database\dao\WebformDao;
use Pageflow\Core\database\dao\WebformDaoMysql;
use Pageflow\Core\modules\webforms\form\WebformForm;
use Pageflow\Core\modules\webforms\handlers\ArticleCommentFormHandler;
use Pageflow\Core\modules\webforms\handlers\EmailFormHandler;
use Pageflow\Core\modules\webforms\handlers\RedirectFormHandler;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformButton;
use Pageflow\Core\modules\webforms\model\WebformTextArea;
use Pageflow\Core\modules\webforms\model\WebformTextfield;
use Pageflow\Core\request_handlers\HttpRequestHandler;

class WebformRequestHandler extends HttpRequestHandler {

    private static string $FORM_QUERYSTRING_KEY = "webform_id";
    private static string $FORM_ID_POST_KEY = "webform_id";
    private WebformHandlerManager $webformHandlerManager;
    private WebformDao $webformDao;
    private ?WebForm $currentWebform = null;
    private ConfigDao $configDao;

    public function __construct() {
        $this->webformDao = WebformDaoMysql::getInstance();
        $this->configDao = ConfigDaoMysql::getInstance();
        $this->webformHandlerManager = WebformHandlerManager::getInstance();
    }

    public function handleGet(): void {
        $this->currentWebform = $this->getFormFromGetRequest();
    }

    public function handlePost(): void {
        $this->currentWebform = $this->getFormFromPostRequest();
        if ($this->isAddWebFormAction()) {
            $this->addWebForm();
        } else if ($this->isAction("update_webform")) {
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("delete_webform")) {
            $this->deleteWebForm($this->currentWebform);
        } else if ($this->isAction("add_textfield")) {
            $this->addTextField($this->currentWebform);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("add_textarea")) {
            $this->addTextArea($this->currentWebform);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("add_button")) {
            $this->addButton($this->currentWebform);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("delete_form_item")) {
            $this->deleteFormItem(intval($_POST['webform_item_to_delete']));
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("add_handler_email_form_handler")) {
            $this->addWebFormHandler(EmailFormHandler::$TYPE);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("add_handler_redirect_form_handler")) {
            $this->addWebFormHandler(RedirectFormHandler::$TYPE);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("add_handler_article_comment_form_handler")) {
            $this->addWebFormHandler(ArticleCommentFormHandler::$TYPE);
            $this->updateWebForm($this->currentWebform);
        } else if ($this->isAction("delete_form_handler")) {
            $this->deleteFormHandler($this->currentWebform);
            $this->updateWebForm($this->currentWebform);
        }

        $this->currentWebform = $this->getFormFromPostRequest();
    }

    public function getCurrentWebForm(): ?WebForm {
        return $this->currentWebform;
    }

    private function getFormFromPostRequest(): ?WebForm {
        $webform = null;
        $webformId = $this->getFormIdFromPostRequest();
        if (!is_null($webformId)) {
            $webform = $this->webformDao->getWebForm($webformId);
        }
        return $webform;
    }

    private function getFormFromGetRequest(): ?WebForm {
        $currentWebform = null;
        if (isset($_GET[self::$FORM_QUERYSTRING_KEY]) && $_GET[self::$FORM_QUERYSTRING_KEY] != "") {
            $currentWebform = $this->webformDao->getWebForm($_GET[self::$FORM_QUERYSTRING_KEY]);
        }
        return $currentWebform;
    }

    private function getFormIdFromPostRequest(): ?int {
        $formId = null;
        if (isset($_POST[self::$FORM_ID_POST_KEY]) && $_POST[self::$FORM_ID_POST_KEY]) {
            $formId = $_POST[self::$FORM_ID_POST_KEY];
        }
        return $formId;
    }

    private function isAddWebFormAction(): bool {
        return isset($_POST["add_webform_action"]) && $_POST["add_webform_action"] == "add_webform";
    }

    private function isAction($name): bool {
        return isset($_POST["action"]) && $_POST["action"] == $name;
    }

    private function addWebForm(): void {
        $webform = new WebForm();
        $webform->setTitle($this->getTextResource("webforms_new_webform_title"));
        $this->webformDao->persistWebForm($webform);
        $this->sendSuccessMessage($this->getTextResource("webforms_new_webform_create_message"));
        $this->redirectTo($this->getBackendBaseUrl() . "&" . self::$FORM_QUERYSTRING_KEY . "=" . $webform->getId());
    }

    private function updateWebForm(WebForm $webform): void {
        $form = new WebformForm($webform);
        try {
            $form->loadFields();
            $this->webformDao->updateWebForm($this->currentWebform);
            foreach ($form->getHandlerProperties() as $property) {
                $this->webformDao->updateHandlerProperty($property);
            }
            if ($this->currentWebform->getIncludeCaptcha()) {
                $this->configDao->updateCaptchaSecret($form->getCaptchaSecret());
            }
            $this->sendSuccessMessage($this->getTextResource("webforms_update_success_message"));
        } catch (FormException) {
            $this->sendErrorMessage($this->getTextResource("webforms_update_error_message"));
        }
    }

    private function deleteWebForm(WebForm $webform): void {
        $this->webformDao->deleteWebForm($webform);
        $this->redirectTo($this->getBackendBaseUrl());
    }

    private function addTextField(WebForm $webform): void {
        $textField = new WebformTextfield();
        $this->webformDao->persistWebFormItem($webform, $textField);
    }

    private function addTextArea(WebForm $webform): void {
        $textArea = new WebFormTextArea();
        $this->webformDao->persistWebFormItem($webform, $textArea);
    }

    private function addButton(WebForm $webform): void {
        $button = new WebformButton();
        $this->webformDao->persistWebFormItem($webform, $button);
    }

    private function deleteFormItem(int $form_item_id): void {
        $this->currentWebform->deleteWebFormItem($form_item_id);
        $this->webformDao->deleteWebFormItem($form_item_id);
    }

    private function addWebFormHandler(string $type): void {
        $handler = $this->webformHandlerManager->getHandler($type);
        $this->webformDao->addWebFormHandler($this->currentWebform, $handler);
    }

    private function deleteFormHandler(WebForm $webform): void {
        $this->webformDao->deleteWebFormHandler($webform, intval($_POST['webform_handler_to_delete']));
    }

}