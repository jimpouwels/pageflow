<?php

namespace Pageflow\Core\modules\templates\dao;

use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\model\TemplateVar;
use Pageflow\Core\modules\templates\model\TemplateVarDef;

class TemplateDaoMysql implements TemplateDao {

    private static ?TemplateDaoMysql $instance = null;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): TemplateDaoMysql {
        if (!self::$instance) {
            self::$instance = new TemplateDaoMysql();
        }
        return self::$instance;
    }

    public function getTemplateVariant(int $id): ?TemplateVariant {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_variants WHERE id = ?");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return TemplateVariant::constructFromRecord($row);
        }
        return null;
    }

    public function getTemplateVariantsByScope(Scope $scope): array {
        $templates = array();
        if ($scope != "") {
            $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_variants WHERE scope_id = ? ORDER BY NAME ASC");
            $scopeId = $scope->getId();
            $statement->bind_param("i", $scopeId);
            $result = $this->mysqlConnector->executeStatement($statement);
            while ($row = $result->fetch_assoc()) {
                $templates[] = TemplateVariant::constructFromRecord($row);
            }
        }

        return $templates;
    }

    public function getTemplateVariants(): array {
        $query = "SELECT * FROM template_variants";
        $result = $this->mysqlConnector->executeQuery($query);
        $templates = array();
        while ($row = $result->fetch_assoc()) {
            $templates[] = TemplateVariant::constructFromRecord($row);
        }
        return $templates;
    }

    public function createTemplateVariant(): TemplateVariant {
        $newTemplateVariant = new TemplateVariant();
        $newTemplateVariant->setScopeId(1);
        $newTemplateVariant->setName("Nieuw template");
        $this->persistTemplateVariant($newTemplateVariant);
        return $newTemplateVariant;
    }

    public function persistTemplateVariant(TemplateVariant $newTemplateVariant): void {
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_variants (scope_id, `name`) VALUES (?, ?)");
        $scopeId = $newTemplateVariant->getScopeId();
        $name = $newTemplateVariant->getName();
        $statement->bind_param("is", $scopeId, $name);
        $this->mysqlConnector->executeStatement($statement);
        $newTemplateVariant->setId($this->mysqlConnector->getInsertId());
    }

    public function updateTemplateVariant(TemplateVariant $templateVariant): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE template_variants SET `name` = ?, template_id = ?, scope_id = ? WHERE id = ?");
        $templateId = $templateVariant->getId();
        $name = $templateVariant->getName();
        $templateFileId = $templateVariant->getTemplateId();
        $scopeId = $templateVariant->getScopeId();
        $statement->bind_param("siii", $name, $templateFileId, $scopeId, $templateId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteTemplateVariant(TemplateVariant $templateVariant): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM template_variants WHERE id = ?");
        $templateId = $templateVariant->getId();
        $statement->bind_param("i", $templateId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getTemplateVariantsForTemplate(Template $template): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_variants WHERE template_id = ?");
        $templateFileId = $template->getId();
        $statement->bind_param('i', $templateFileId);
        $result = $this->mysqlConnector->executeStatement($statement);

        $templates = array();
        while ($row = $result->fetch_assoc()) {
            $templates[] = TemplateVariant::constructFromRecord($row);
        }
        return $templates;
    }

    public function getTemplateVars(TemplateVariant $template): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_vars WHERE template_id = ?");
        $templateId = $template->getId();
        $statement->bind_param("i", $templateId);
        $result = $this->mysqlConnector->executeStatement($statement);

        $templateVars = array();
        while ($row = $result->fetch_assoc()) {
            $templateVars[] = TemplateVar::constructFromRecord($row);
        }
        return $templateVars;
    }

    public function storeTemplateVar(TemplateVariant $template, string $name, ?string $value = ""): TemplateVar {
        $newTemplateVar = new TemplateVar();
        $newTemplateVar->setName($name);
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_vars (`name`, `value`, template_id) VALUES (?, ?, ?)");
        $templateVariantId = $template->getId();
        $statement->bind_param("ssi", $name, $value, $templateVariantId);
        $this->mysqlConnector->executeStatement($statement);
        $newTemplateVar->setId($this->mysqlConnector->getInsertId());
        return $newTemplateVar;
    }

    public function updateTemplateVar(TemplateVar $templateVar): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE template_vars SET `value` = ? WHERE id = ?");
        $value = $templateVar->getValue();
        $id = $templateVar->getId();
        $statement->bind_param("si", $value, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteTemplateVar(TemplateVar $templateVar): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM template_vars WHERE id = ?");
        $id = $templateVar->getId();
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getTemplate(int $id): ?Template {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates WHERE id = ?");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return Template::constructFromRecord($row);
        }
        return null;
    }

    public function getTemplates(): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates ORDER BY name ASC");
        $result = $this->mysqlConnector->executeStatement($statement);

        $templateFiles = array();
        while ($row = $result->fetch_assoc()) {
            $templateFiles[] = Template::constructFromRecord($row);
        }
        return $templateFiles;
    }

    public function getTemplateFile(int $id): ?Template {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates WHERE id = ?");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return Template::constructFromRecord($row);
        }
        return null;
    }

    public function storeTemplate(Template $template): void {
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO templates (`name`) VALUES (?)");
        $name = $template->getName();
        $statement->bind_param("s", $name);
        $this->mysqlConnector->executeStatement($statement);
        $template->setId($this->mysqlConnector->getInsertId());
    }

    public function deleteTemplate(Template $template): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM templates WHERE id = ?");
        $id = $template->getId();
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateTemplate(Template $template): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE templates SET `name` = ?, `code` = ?, `filename` = ? WHERE id = ?");
        $id = $template->getId();
        $name = $template->getName();
        $code = $template->getCode();
        $filename = $template->getFileName();
        $statement->bind_param("sssi", $name, $code, $filename, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getTemplateVarDefs(Template $template): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_var_defs WHERE template_id = ?");
        $templateFileId = $template->getId();
        $statement->bind_param("i", $templateFileId);
        $result = $this->mysqlConnector->executeStatement($statement);

        $templateVarDefs = array();
        while ($row = $result->fetch_assoc()) {
            $templateVarDefs[] = TemplateVarDef::constructFromRecord($row);
        }
        return $templateVarDefs;
    }

    public function storeTemplateVarDef(Template $template, string $varDefName): TemplateVarDef {
        $varDef = new TemplateVarDef();
        $varDef->setName($varDefName);
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_var_defs (`name`, template_id) VALUES (?, ?)");
        $templateFileId = $template->getId();
        $statement->bind_param("si", $varDefName, $templateFileId);
        $this->mysqlConnector->executeStatement($statement);
        $varDef->setId($this->mysqlConnector->getInsertId());
        return $varDef;
    }

    public function updateTemplateVarDef(TemplateVarDef $templateVarDef): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE template_var_defs SET default_value = ? WHERE id = ?");
        $id = $templateVarDef->getId();
        $defaultValue = $templateVarDef->getDefaultValue();
        $statement->bind_param("si", $defaultValue, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteTemplateVarDef(TemplateVarDef $template_var_def): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM template_var_defs WHERE id = ?");
        $id = $template_var_def->getId();
        $statement->bind_param('i', $id);
        $this->mysqlConnector->executeStatement($statement);
    }
}
