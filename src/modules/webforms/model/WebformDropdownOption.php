<?php

namespace Pageflow\Core\modules\webforms\model;

use Pageflow\Core\core\model\Entity;

class WebformDropdownOption extends Entity {

    private string $text;
    private string $name;

    public function __construct(string $text, string $name) {
        $this->text = $text;
        $this->name = $name;
    }

    public function getText(): string {
        return $this->text;
    }

    public function setText(string $text): void {
        $this->text = $text;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

}