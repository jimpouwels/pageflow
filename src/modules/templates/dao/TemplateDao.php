<?php

namespace Pageflow\Core\modules\templates\dao;

use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\model\TemplateVar;
use Pageflow\Core\modules\templates\model\TemplateVarDef;

interface TemplateDao {
    public function getTemplateVariant(int $id): ?TemplateVariant;

    public function getTemplateVariantsByScope(Scope $scope): array;

    public function getTemplateVariants(): array;

    public function createTemplateVariant(): TemplateVariant;

    public function persistTemplateVariant(TemplateVariant $newTemplate): void;

    public function updateTemplateVariant(TemplateVariant $template): void;

    public function deleteTemplateVariant(TemplateVariant $template): void;

    public function getTemplateVariantsForTemplate(Template $template): array;

    public function getTemplateVars(TemplateVariant $template): array;

    public function storeTemplateVar(TemplateVariant $template, string $name, ?string $value = ""): TemplateVar;

    public function updateTemplateVar(TemplateVar $templateVar): void;

    public function deleteTemplateVar(TemplateVar $templateVar): void;

    public function getTemplates(): array;

    public function getTemplate(int $id): ?Template;

    public function storeTemplate(Template $template): void;

    public function deleteTemplate(Template $template): void;

    public function updateTemplate(Template $template): void;

    public function getTemplateVarDefs(Template $template): array;

    public function storeTemplateVarDef(Template $template, string $varDefName): TemplateVarDef;

    public function updateTemplateVarDef(TemplateVarDef $templateVarDef): void;

    public function deleteTemplateVarDef(TemplateVarDef $templateVarDef): void;
}