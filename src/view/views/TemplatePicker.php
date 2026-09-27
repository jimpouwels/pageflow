<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;

class TemplatePicker extends Pulldown {

    public function __construct(string $name, string $label, bool $mandatory, ?string $className, ?TemplateVariant $currentTemplateVariant, Scope $scope) {
        $options = $this->getOptions($scope);
        $current_template_id = null;
        if (!is_null($currentTemplateVariant)) {
            $current_template_id = $currentTemplateVariant->getId();
        }
        parent::__construct($name, $label, $current_template_id, $options, $mandatory, $className, true);
    }

    private function getOptions(Scope $scope): array {
        $template_dao = TemplateDaoMysql::getInstance();
        $options = array();
        foreach ($template_dao->getTemplateVariantsByScope($scope) as $template) {
            $options[] = array('name' => $template->getName(), 'value' => $template->getId());
        }
        return $options;
    }

}