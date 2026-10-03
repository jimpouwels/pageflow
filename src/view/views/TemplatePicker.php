<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;
use Pageflow\Core\modules\templates\model\Scope;

class TemplatePicker extends Pulldown {

    private TemplateService $templateService;

    public function __construct(string $name, string $label, ?int $currentTemplateVariantId, bool $mandatory, ?string $className, Scope $scope) {
        $this->templateService = TemplateInteractor::getInstance();
        $options = $this->getOptions($scope);
        parent::__construct($name, $label, $currentTemplateVariantId, $options, $mandatory, $className, true);
    }

    private function getOptions(Scope $scope): array {
        $options = array();
        
        foreach ($this->templateService->getTemplateVariantsForScope($scope) as $templateVariant) {
            $options[] = array('name' => $templateVariant->getName(), 'value' => $templateVariant->getId());
        }
        return $options;
    }

}
