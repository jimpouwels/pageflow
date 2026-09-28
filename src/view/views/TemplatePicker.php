<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\modules\templates\dao\TemplateDaoMysql;
use Pageflow\Core\modules\templates\model\Scope;
use Pageflow\Core\modules\templates\model\TemplateVariant;

class TemplatePicker extends Pulldown {

    public function __construct(string $name, string $label, ?int $currentTemplateVariantId, bool $mandatory, ?string $className, Scope $scope) {
        $options = $this->getOptions($scope);
        parent::__construct($name, $label, $currentTemplateVariantId, $options, $mandatory, $className, true);
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
