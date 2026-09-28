<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;

class TemplatePicker extends Pulldown {

    public function __construct(string $name, string $label, bool $mandatory, ?string $className, ?TemplateVariant $currentTemplateVariant, Scope $scope) {
        $options = $this->getOptions($scope);
        $currentTemplateId = null;
        if (!is_null($currentTemplateVariant)) {
            $currentTemplateId = $currentTemplateVariant->getId();
        }
        parent::__construct($name, $label, $currentTemplateId, $options, $mandatory, $className, true);
    }

    private function getOptions(Scope $scope): array {
        $templateDao = TemplateDaoMysql::getInstance();
        $options = array();
        foreach ($templateDao->getTemplateVariantsByScope($scope) as $template) {
            $options[] = array('name' => $template->getName(), 'value' => $template->getId());
        }
        return $options;
    }

}
