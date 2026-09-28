<?php

namespace Pageflow\Core\modules\webforms\model;

use Pageflow\Core\modules\templates\model\Presentable;

abstract class WebformItem extends Presentable {

    private string $label = "";
    private string $name = "";
    private int $orderNr = 0;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId);
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getOrderNr(): int {
        return $this->orderNr;
    }

    public function setOrderNr(int $orderNr): void {
        $this->orderNr = $orderNr;
    }

    public abstract function getType(): string;

    protected function initFromDb(array $row): void {
        $this->setName($row['name']);
        $this->setLabel($row['label']);
        $this->setOrderNr($row['order_nr']);
        parent::initFromDb($row);
    }

}