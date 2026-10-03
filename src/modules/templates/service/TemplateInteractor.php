<?php

namespace Pageflow\Core\modules\templates\service;


use Pageflow\Core\modules\templates\dao\TemplateDao;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVar;
use Pageflow\Core\modules\templates\model\TemplateVarDef;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\utilities\Arrays;

class TemplateInteractor implements TemplateService {

    private static ?TemplateInteractor $instance = null;

    private TemplateDao $templateDao;

    private function __construct() {
        $this->templateDao = TemplateDaoMysql::getInstance();
    }

    public static function getInstance(): TemplateInteractor {
        if (!self::$instance) {
            self::$instance = new TemplateInteractor();
        }
        return self::$instance;
    }

    public function getTemplateForPresentable(Presentable $presentable): ?Template {
        $templateVariant = $this->templateDao->getTemplateVariant($presentable->getTemplateId());
        $templateId = $templateVariant?->getTemplateId();
        return $templateId ? $this->templateDao->getTemplate($templateId) : null;
    }

    public function getTemplateVarDefByTemplateVar(TemplateVariant $template, TemplateVar $templateVar): TemplateVarDef {
        return Arrays::firstMatch($this->getTemplateVarDefsByTemplate($template), function ($templateVarDef) use ($templateVar) {
            return $templateVar->getName() == $templateVarDef->getName();
        });
    }

    public function getTemplateVarDefsByTemplate(TemplateVariant $template): array {
        $templateFile = $this->getTemplateForTemplateVariant($template);
        return $templateFile ? $this->templateDao->getTemplateVarDefs($templateFile) : [];
    }

    public function getTemplateForTemplateVariant(TemplateVariant $templateVariant): ?Template {
        $templateId = $templateVariant->getTemplateId();
        return $templateId ? $this->templateDao->getTemplate($templateId) : null;
    }

    public function getTemplateVariantsForScope(Scope $scope): array {
        return $this->templateDao->getTemplateVariantsByScope($scope);
    }

    public function getTemplates(): array {
        return $this->templateDao->getTemplates();
    }

    public function getTemplatesByScope(Scope $scope): array {
        return array_filter($this->getTemplates(), fn($template) => $template->getScopeId() == $scope->getId());
    }

    public function getTemplateVariant(?int $templateVariantId): ?TemplateVariant {
        if (!$templateVariantId) {
            return null;
        }
        return $this->templateDao->getTemplateVariant($templateVariantId);
    }

    public function getTemplateVariantsForTemplate(Template $template): array {
        return $this->templateDao->getTemplateVariantsForTemplate($template);
    }

    public function getUnassignedTemplateVariants(): array {
        return $this->templateDao->getUnassignedTemplateVariants();
    }
}