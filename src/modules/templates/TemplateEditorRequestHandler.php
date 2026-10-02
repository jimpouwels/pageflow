<?php

namespace Pageflow\Core\modules\templates;

use Pageflow\Core\core\form\FormException;
use Pageflow\Core\database\dao\ScopeDao;
use Pageflow\Core\database\dao\ScopeDaoMysql;
use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;

class TemplateEditorRequestHandler extends HttpRequestHandler {

    private static string $TEMPLATE_VARIANT_ID_GET = "template_variant";
    private static string $SCOPE_IDENTIFIER_GET = "scope";
    private static string $UNASSIGNED_GET = "unassigned";
    private static string $TEMPLATE_VARIANT_ID_POST = "template_variant_id";

    private TemplateDao $templateDao;
    private TemplateService $templateService;
    private ScopeDao $scopeDao;
    private ?TemplateVariant $currentTemplateVariant = null;
    private ?Scope $currentScope = null;
    private bool $showUnassigned = false;

    public function __construct() {
        $this->templateDao = TemplateDaoMysql::getInstance();
        $this->scopeDao = ScopeDaoMysql::getInstance();
        $this->templateService = TemplateInteractor::getInstance();
    }

    public function handleGet(): void {
        if ($this->isCurrentTemplateVariantShown()) {
            $this->currentTemplateVariant = $this->getTemplateVariantFromGetRequest();
        }
        $this->showUnassigned = isset($_GET[self::$UNASSIGNED_GET]);
        $this->currentScope = $this->resolveScope();
    }

    public function handlePost(): void {
        $this->currentTemplateVariant = $this->getTemplateFromPostRequest();
        $this->currentScope = $this->resolveScope();
        if ($this->isUpdateAction()) {
            $this->updateTemplateVariant();
        } else if ($this->isAddTemplateAction()) {
            $this->addTemplateVariant();
        } else if ($this->isDeleteAction()) {
            $this->deleteTemplateVariants();
        }
    }

    public function getCurrentTemplateVariant(): ?TemplateVariant {
        return $this->currentTemplateVariant;
    }

    public function getCurrentScope(): ?Scope {
        return $this->currentScope;
    }

    public function getShowUnassigned(): bool {
        return $this->showUnassigned;
    }

    private function addTemplateVariant(): void {
        $newTemplate = $this->templateDao->createTemplateVariant();
        $this->sendSuccessMessage("Template succesvol aangemaakt");
        $this->redirectTo($this->getBackendBaseUrl() . "&" . self::$TEMPLATE_VARIANT_ID_GET . "=" . $newTemplate->getId());
    }

    private function deleteTemplateVariants(): void {
        $deletedAny = false;
        foreach ($this->templateDao->getTemplateVariants() as $templateVariant) {
            if (isset($_POST["template_variant_" . $templateVariant->getId() . "_delete"])) {
                $this->templateDao->deleteTemplateVariant($templateVariant);
                $deletedAny = true;
            }
        }
        if (!$deletedAny && !is_null($this->currentTemplateVariant)) {
            $scope = $this->currentScope;
            $this->templateDao->deleteTemplateVariant($this->currentTemplateVariant);
            $this->currentTemplateVariant = null;
            $this->sendSuccessMessage("Template(s) succesvol verwijderd");
            $redirectUrl = $this->getBackendBaseUrl();
            if (!is_null($scope)) {
                $redirectUrl .= "&scope=" . $scope->getIdentifier();
            }
            $this->redirectTo($redirectUrl);
        }
        $this->sendSuccessMessage("Template(s) succesvol verwijderd");
    }

    private function updateTemplateVariant(): void {
        $templateVariantForm = new TemplateVariantEditorForm($this->currentTemplateVariant);
        try {
            $templateVariantForm->loadFields();
            $this->templateDao->updateTemplateVariant($this->currentTemplateVariant);
            $this->sendSuccessMessage("Template variant succesvol opgeslagen");
        } catch (FormException $e) {
            $this->sendErrorMessage("Template variant niet opgeslagen, verwerk de fouten");
        }
    }

    private function resolveScope(): ?Scope {
        if ($this->showUnassigned) {
            return null;
        }
        $scope = $this->getScopeFromGetRequest();
        if (is_null($scope) && !is_null($this->currentTemplateVariant)) {
            $templateFile = $this->templateService->getTemplateForTemplateVariant($this->currentTemplateVariant);
            if ($templateFile) {
                $scope = $this->scopeDao->getScope($templateFile->getScopeId());
            }
        }
        return $scope;
    }

    private function getTemplateFromPostRequest(): ?TemplateVariant {
        $templateVariant = null;
        if (isset($_POST[self::$TEMPLATE_VARIANT_ID_POST])) {
            $templateVariant = $this->templateDao->getTemplateVariant(intval($_POST[self::$TEMPLATE_VARIANT_ID_POST]));
        }
        return $templateVariant;
    }

    private function getTemplateVariantFromGetRequest(): TemplateVariant {
        return $this->templateDao->getTemplateVariant($_GET[self::$TEMPLATE_VARIANT_ID_GET]);
    }

    private function getScopeFromGetRequest(): ?Scope {
        if (isset($_GET[self::$SCOPE_IDENTIFIER_GET])) {
            $scopeIdentifier = $_GET[self::$SCOPE_IDENTIFIER_GET];
            return $this->scopeDao->getScopeByIdentifier($scopeIdentifier);
        }
        return null;
    }

    private function isCurrentTemplateVariantShown(): bool {
        return isset($_GET[self::$TEMPLATE_VARIANT_ID_GET]);
    }

    private function isUpdateAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "update_template_variant";
    }

    private function isAddTemplateAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "add_template_variant";
    }

    private function isDeleteAction(): bool {
        return isset($_POST["action"]) && $_POST["action"] == "delete_template_variants";
    }

}