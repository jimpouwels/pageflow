<?php

namespace Pageflow\Core\modules\templates\model;

use Pageflow\Core\core\model\Entity;
use Pageflow\Core\database\dao\TemplateDaoMysql;
use const Pageflow\core\FRONTEND_TEMPLATE_DIR;

class TemplateFile extends Entity {

    private ?string $fileName = null;
    private string $name;
    private ?string $code;
    private array $templateVarDefs = array();

    public static function constructFromRecord(array $row): TemplateFile {
        $template = new TemplateFile();
        $template->initFromDb($row);
        return $template;
    }

    protected function initFromDb(array $row): void {
        $this->setFileName($row['filename']);
        $this->setName($row['name']);
        $this->setCode($row['code']);
        parent::initFromDb($row);
        $this->setTemplateVarDefs(TemplateDaoMysql::getInstance()->getTemplateVarDefs($this));
    }

    public function getTemplateVarDefs(): array {
        return $this->templateVarDefs;
    }

    public function setTemplateVarDefs(array $templateVarDefs): void {
        $this->templateVarDefs = $templateVarDefs;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getTemplateFileCode(): ?string {
        $code = "";
        $filepath = FRONTEND_TEMPLATE_DIR . '/' . $this->getFilename();
        if (is_file($filepath) && file_exists($filepath)) {
            $code = file_get_contents($filepath);
        }
        return $code;
    }

    public function getFileName(): ?string {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void {
        $this->fileName = $fileName;
    }

    public function getTemplateVarDef(string $varName): TemplateVarDef {
        return array_filter($this->templateVarDefs, fn($templateVarDef) => $varName == $templateVarDef->getName())[0];
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setCode(?string $code): void {
        $this->code = $code;
    }

    public function addTemplateVarDef(TemplateVarDef $templateVarDef): void {
        $this->templateVarDefs[] = $templateVarDef;
    }

    public function deleteTemplateVarDef(TemplateVarDef $templateVarDefToDelete): void {
        $this->templateVarDefs = array_filter($this->templateVarDefs, fn($templateVarDef) => $templateVarDef->getId() !== $templateVarDefToDelete->getId());
    }

}