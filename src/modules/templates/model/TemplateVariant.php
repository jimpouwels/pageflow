<?php

namespace Pageflow\Core\modules\templates\model;

use Pageflow\Core\core\model\Entity;
use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;

class TemplateVariant extends Entity {

    private string $name;
    private array $templateVars = array();
    private ?int $templateFileId = null;

    public static function constructFromRecord(array $row): TemplateVariant {
        $template = new TemplateVariant();
        $template->initFromDb($row);
        return $template;
    }

    protected function initFromDb(array $row): void {
        $this->setName($row['name']);
        $this->setTemplateId($row['template_id']);
        parent::initFromDb($row);
        $this->setTemplateVars(TemplateDaoMysql::getInstance()->getTemplateVars($this));
    }

    public function getTemplateVars(): array {
        return $this->templateVars;
    }

    public function setTemplateVars(array $templateVars): void {
        $this->templateVars = $templateVars;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getTemplateId(): ?int {
        return $this->templateFileId;
    }

    public function setTemplateId(?int $templateId): void {
        $this->templateFileId = $templateId;
    }

    public function addTemplateVar(TemplateVar $templateVar): void {
        $this->templateVars[] = $templateVar;
    }

    public function deleteTemplateVar(TemplateVar $templateVarToDelete): void {
        $this->templateVars = array_filter($this->templateVars, function ($templateVar) use ($templateVarToDelete) {
            return $templateVar->getId() !== $templateVarToDelete->getId();
        });
    }

}