<?php

namespace Pageflow\Core\modules\webforms\model;

use Pageflow\Core\core\model\Entity;
use Pageflow\Core\utilities\Arrays;

class WebformHandlerInstance extends Entity {

    private string $type;
    private array $properties;
    private int $orderNr = 0;

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getOrderNr(): int {
        return $this->orderNr;
    }

    public function setOrderNr(int $orderNr): void {
        $this->orderNr = $orderNr;
    }

    public function setProperties(array $properties): void {
        $this->properties = $properties;
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function getProperty(string $propertyToFind): ?WebformHandlerProperty {
        return Arrays::firstMatch($this->properties, fn($p) => $propertyToFind == $p->getName());
    }

    public static function constructFromRecord(array $row, array $properties): WebformHandlerInstance {
        $webformHandlerInstance = new WebformHandlerInstance();
        $webformHandlerInstance->setProperties($properties);
        $webformHandlerInstance->initFromDb($row);
        return $webformHandlerInstance;
    }

    protected function initFromDb(array $row): void {
        $this->setType($row['type']);
        $this->setOrderNr($row['order_nr'] ?? 0);
        parent::initFromDb($row);
    }
}