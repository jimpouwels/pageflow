<?php

namespace Pageflow\Core\modules\webforms\model;

abstract class WebformField extends WebformItem {

    private bool $mandatory = false;

    public function getMandatory(): bool {
        return $this->mandatory;
    }

    public function setMandatory(bool $mandatory): void {
        $this->mandatory = $mandatory;
    }

    protected function initFromDb(array $row): void {
        $this->setMandatory($row["mandatory"] == 1);
        parent::initFromDb($row);
    }

}