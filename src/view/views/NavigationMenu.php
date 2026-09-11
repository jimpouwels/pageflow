<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\core\model\ModuleGroup;

class NavigationMenu extends Visual {

    private array $moduleGroups;
    private ?int $activeModuleId;

    public function __construct(array $moduleGroups, ?int $activeModuleId = null) {
        parent::__construct();
        $this->moduleGroups = $moduleGroups;
        $this->activeModuleId = $activeModuleId;
    }

    public function getTemplateFilename(): string {
        return "navigation_menu.tpl";
    }

    public function load(): void {
        $groups = array();
        foreach ($this->moduleGroups as $moduleGroup) {
            // Skip insert group - now handled by inline insert buttons
            if ($moduleGroup->isElementGroup()) {
                continue;
            }
            
            $group = array();
            $group['title'] = $this->getTextResource('menu_item_' . $moduleGroup->getIdentifier());
            $group['modules'] = $this->renderMenuItem($moduleGroup);
            $groups[] = $group;
        }
        $this->assign('groups', $groups);
    }

    private function renderMenuItem(ModuleGroup $moduleGroup): array {
        $subItems = array();
        $modules = $moduleGroup->getModules();
        $count = 1;
        foreach ($modules as $module) {
            $subItem = array();
            $subItem["title"] = $this->getTextResource($module->getIdentifier() . '_module_title');
            $subItem["id"] = $module->getId();
            $subItem["icon_url"] = '/admin?file=/modules/' . $module->getIdentifier() . '/img/' . $module->getIdentifier() . '.png';
            $subItem["last"] = ($count == count($modules));
            $subItem["active"] = ($module->getId() == $this->activeModuleId);
            $count++;
            $subItems[] = $subItem;
        }
        return $subItems;
    }

}