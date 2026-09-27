<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\request_handlers\HttpRequestHandler;

class TemplateRequestHandler extends HttpRequestHandler {

    private static string $TEMPLATE_ID_GET = "template";
    private static string $TEMPLATE_ID_POST = "template_id";

    private TemplateDao $templateDao;
    private ?Template $currentTemplate = null;
    private array $parsedVarDefs = array();

    public function __construct() {
        $this->templateDao = TemplateDaoMysql::getInstance();
    }

    public function handleGet(): void {
        if ($this->isCurrentTemplateShown()) {
            $this->currentTemplate = $this->getTemplateFromGetRequest();
        }
    }

    public function handlePost(): void {
        $this->currentTemplate = $this->getTemplateFromPostRequest();
        if ($this->isAddAction()) {
            $this->addTemplate();
        } else if ($this->isUpdateAction()) {
            $this->updateTemplate();
        } else if ($this->isReloadAction()) {
            $this->reloadTemplate();
        } else if ($this->isDeleteAction()) {
            $this->deleteTemplate();
        }
    }

    public function getCurrentTemplate(): ?Template {
        return $this->currentTemplate;
    }

    public function getParsedVarDefs(): array {
        return $this->parsedVarDefs;
    }

    private function addTemplate(): void {
        $template = new Template();
        $template->setName($this->getTextResource('templates_new_name'));
        $this->templateDao->storeTemplate($template);
        $this->redirectTo($this->getBackendBaseUrl() . "&template=" . $template->getId());
    }

    private function updateTemplate(): void {
        $templateForm = new TemplateForm($this->currentTemplate);
        $templateForm->loadFields();
        $this->templateDao->updateTemplate($this->currentTemplate);
        $this->sendSuccessMessage($this->getTextResource('message_template_successfully_saved'));
    }

    private function reloadTemplate(): void {
        $templateForm = new TemplateForm($this->currentTemplate);
        $templateForm->setReloading();
        $templateForm->loadFields();
        $this->parsedVarDefs = $templateForm->getParseVarDefs();
    }

    private function deleteTemplate(): void {
        $this->templateDao->deleteTemplate($this->currentTemplate);
        $this->sendSuccessMessage($this->getTextResource('message_template_successfully_deleted'));
        $this->redirectTo($this->getBackendBaseUrl());
    }

    private function getTemplateFromPostRequest(): ?Template {
        $template = null;
        if (isset($_POST[self::$TEMPLATE_ID_POST])) {
            $id = intval($_POST[self::$TEMPLATE_ID_POST]);
            $template = $this->templateDao->getTemplate($id);
        }
        return $template;
    }

    private function getTemplateFromGetRequest(): ?Template {
        $template = null;
        if (isset($_GET[self::$TEMPLATE_ID_GET])) {
            $template = $this->templateDao->getTemplate($_GET[self::$TEMPLATE_ID_GET]);
        }
        return $template;
    }

    private function isCurrentTemplateShown(): bool {
        return isset($_GET[self::$TEMPLATE_ID_GET]);
    }

    private function isUpdateAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "update_template";
    }

    private function isAddAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "add_template";
    }

    private function isDeleteAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "delete_template";
    }

    private function isReloadAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "reload_template";
    }

}