<?php

namespace Pageflow\Core\modules\templates\service;

use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\model\TemplateVar;
use Pageflow\Core\modules\templates\model\TemplateVarDef;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\modules\templates\model\Scope;

interface TemplateService {
    public function getTemplateForPresentable(Presentable $presentable): ?Template;

    public function getTemplateVarDefByTemplateVar(TemplateVariant $template, TemplateVar $templateVar): TemplateVarDef;

    public function getTemplateVarDefsByTemplate(TemplateVariant $template): array;

    public function getTemplateForTemplateVariant(TemplateVariant $templateVariant): ?Template;

    public function getTemplates(): array;

    public function getTemplatesByScope(Scope $scope): array;

    public function getTemplateVariant(?int $templateVariantId): ?TemplateVariant;

    public function getTemplateVariantsForTemplate(Template $template): array;
}