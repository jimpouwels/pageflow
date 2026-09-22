<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\Template;
use Pageflow\Core\modules\templates\model\TemplateFile;
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

    public function getTemplate(int $id): ?Template {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates WHERE id = ?");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return Template::constructFromRecord($row);
        }
        return null;
    }

    public function getTemplatesByScope(Scope $scope): array {
        $templates = array();
        if ($scope != "") {
            $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates WHERE scope_id = ? ORDER BY NAME ASC");
            $scopeId = $scope->getId();
            $statement->bind_param("i", $scopeId);
            $result = $this->mysqlConnector->executeStatement($statement);
            while ($row = $result->fetch_assoc()) {
                $templates[] = Template::constructFromRecord($row);
            }
        }

        return $templates;
    }

    public function getTemplates(): array {
        $query = "SELECT * FROM templates";
        $result = $this->mysqlConnector->executeQuery($query);
        $templates = array();
        while ($row = $result->fetch_assoc()) {
            $templates[] = Template::constructFromRecord($row);
        }
        return $templates;
    }

    public function createTemplate(): Template {
        $newTemplate = new Template();
        $newTemplate->setScopeId(1);
        $newTemplate->setName("Nieuw template");
        $this->persistTemplate($newTemplate);
        return $newTemplate;
    }

    public function persistTemplate(Template $newTemplate): void {
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO templates (scope_id, `name`) VALUES (?, ?)");
        $scopeId = $newTemplate->getScopeId();
        $name = $newTemplate->getName();
        $statement->bind_param("is", $scopeId, $name);
        $this->mysqlConnector->executeStatement($statement);
        $newTemplate->setId($this->mysqlConnector->getInsertId());
    }

    public function updateTemplate(Template $template): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE templates SET `name` = ?, template_file_id = ?, scope_id = ? WHERE id = ?");
        $templateId = $template->getId();
        $name = $template->getName();
        $templateFileId = $template->getTemplateFileId();
        $scopeId = $template->getScopeId();
        $statement->bind_param("siii", $name, $templateFileId, $scopeId, $templateId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteTemplate(Template $template): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM templates WHERE id = ?");
        $templateId = $template->getId();
        $statement->bind_param("i", $templateId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getTemplatesForTemplateFile(TemplateFile $templateFile): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM templates WHERE template_file_id = ?");
        $templateFileId = $templateFile->getId();
        $statement->bind_param('i', $templateFileId);
        $result = $this->mysqlConnector->executeStatement($statement);

        $templates = array();
        while ($row = $result->fetch_assoc()) {
            $templates[] = Template::constructFromRecord($row);
        }
        return $templates;
    }

    public function getTemplateVars(Template $template): array {
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

    public function storeTemplateVar(Template $template, string $name, ?string $value = ""): TemplateVar {
        $newTemplateVar = new TemplateVar();
        $newTemplateVar->setName($name);
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_vars (`name`, `value`, template_id) VALUES (?, ?, ?)");
        $templateId = $template->getId();
        $statement->bind_param("ssi", $name, $value, $templateId);
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

    public function getTemplateFiles(): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_files");
        $result = $this->mysqlConnector->executeStatement($statement);

        $templateFiles = array();
        while ($row = $result->fetch_assoc()) {
            $templateFiles[] = TemplateFile::constructFromRecord($row);
        }
        return $templateFiles;
    }

    public function getTemplateFile(int $id): ?TemplateFile {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_files WHERE id = ?");
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return TemplateFile::constructFromRecord($row);
        }
        return null;
    }

    public function storeTemplateFile(TemplateFile $templateFile): void {
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_files (`name`) VALUES (?)");
        $name = $templateFile->getName();
        $statement->bind_param("s", $name);
        $this->mysqlConnector->executeStatement($statement);
        $templateFile->setId($this->mysqlConnector->getInsertId());
    }

    public function deleteTemplateFile(TemplateFile $templateFile): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM template_files WHERE id = ?");
        $id = $templateFile->getId();
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateTemplateFile(TemplateFile $templateFile): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE template_files SET `name` = ?, `code` = ?, `filename` = ? WHERE id = ?");
        $id = $templateFile->getId();
        $name = $templateFile->getName();
        $code = $templateFile->getCode();
        $filename = $templateFile->getFileName();
        $statement->bind_param("sssi", $name, $code, $filename, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getTemplateVarDefs(TemplateFile $template_file): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT * FROM template_var_defs WHERE template_file_id = ?");
        $templateFileId = $template_file->getId();
        $statement->bind_param("i", $templateFileId);
        $result = $this->mysqlConnector->executeStatement($statement);

        $templateVarDefs = array();
        while ($row = $result->fetch_assoc()) {
            $templateVarDefs[] = TemplateVarDef::constructFromRecord($row);
        }
        return $templateVarDefs;
    }

    public function storeTemplateVarDef(TemplateFile $templateFile, string $varDefName): TemplateVarDef {
        $varDef = new TemplateVarDef();
        $varDef->setName($varDefName);
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO template_var_defs (`name`, template_file_id) VALUES (?, ?)");
        $templateFileId = $templateFile->getId();
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
