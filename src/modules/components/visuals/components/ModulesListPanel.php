<?php

namespace Pageflow\Core\modules\components\visuals\components;

use Pageflow\Core\database\dao\ModuleDao;
use Pageflow\Core\database\dao\ModuleDaoMysql;
use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\modules\components\ComponentRequestHandler;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;

class ModulesListPanel extends Panel {

    private ModuleDao $moduleDao;
    private ComponentRequestHandler $componentsRequestHandler;

    public function __construct($requestHandler) {
        parent::__construct('Modules', 'component-list-fieldset');
        $this->componentsRequestHandler = $requestHandler;
        $this->moduleDao = ModuleDaoMysql::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return 'components/templates/components/modules_list.tpl';
    }

    public function loadPanelContent(TemplateData $data): void {
        $data->assign('modules', $this->getModulesData());
    }

    private function getModulesData(): array {
        $modules_data = array();
        foreach ($this->moduleDao->getAllModules() as $module) {
            $module_data = array();
            $module_data['id'] = $module->getId();
            $module_data['title'] = $this->getTextResource($module->getIdentifier() . '_module_title');
            $module_data['icon_url'] = BlackBoard::getModuleIconUrl($module->getIdentifier());
            $module_data['is_current'] = $this->isCurrentModule($module);
            $modules_data[] = $module_data;
        }
        return $modules_data;
    }

    private function isCurrentModule($module): bool {
        $current_module = $this->componentsRequestHandler->getCurrentModule();
        return $current_module && $current_module->getId() == $module->getId();
    }
}
