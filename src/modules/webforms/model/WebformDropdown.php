<?php

namespace Pageflow\Core\modules\webforms\model;

class WebformDropdown extends WebformField {

    public static string $TYPE = "dropdown";
    private static int $SCOPE = 15;
    private array $options = array();

    public function __construct() {
        parent::__construct(self::$SCOPE);
    }

    public static function constructFromRecord(array $row): WebformDropdown {
        $field = new WebformDropdown();
        $field->initFromDb($row);
        return $field;
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function setOptions(array $options): void {
        $this->options = $options;
    }

    public function addOption(WebformDropdownOption $option) {
        $this->options[] = $option;
    }

    public function getType(): string {
        return self::$TYPE;
    }

}