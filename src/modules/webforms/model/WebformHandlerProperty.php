<?php

namespace Pageflow\Core\modules\webforms\model;

use Pageflow\Core\core\model\Entity;

class WebformHandlerProperty extends Entity {

    private string $name;
    private ?string $value = null;
    private string $type;

    public static function constructFromRecord(array $row): WebformHandlerProperty {
        $webformHandlerProperty = new WebformHandlerProperty();
        $webformHandlerProperty->initFromDb($row);
        return $webformHandlerProperty;
    }

    protected function initFromDb(array $row): void {
        $this->setName($row['name']);
        $this->setValue($row['value']);
        $this->setType($row['type']);
        parent::initFromDb($row);
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getValue(): ?string {
        return $this->value;
    }

    public function setValue(?string $value): void {
        $this->value = $value;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }
}