<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\authentication\Session;
use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\database\dao\ModuleDao;
use Pageflow\Core\database\dao\ModuleDaoMysql;
use Pageflow\Core\database\dao\SettingsDaoMysql;
use Pageflow\Core\modules\settings\model\Settings;
use Pageflow\Core\utilities\Logs;
use const Pageflow\core\SYSTEM_VERSION;

class Cms extends Visual {
    private ?ModuleVisual $moduleVisual;
    private Settings $settings;
    private ModuleDao $moduleDao;

    public function __construct(?ModuleVisual $moduleVisual) {
        parent::__construct();
        $this->moduleDao = ModuleDaoMysql::getInstance();
        $this->moduleVisual = $moduleVisual;
        $this->settings = SettingsDaoMysql::getInstance()->getSettings();
    }

    public function getTemplateFilename(): string {
        return "cms.tpl";
    }

    public function load(): void {
        $navigation_menu = new NavigationMenu($this->moduleDao->getModuleGroups(), BlackBoard::$MODULE_ID);
        $notification_bar = new NotificationBar();
        $currentUserIndicator = new CurrentUserIndicator();

        $this->assignGlobal("text_resources", Session::getTextResources());

        // Load main CSS inline
        $this->assignGlobal("main_css", file_get_contents(__DIR__ . '/../../static/default/css/styles.css'));

        $this->assignGlobal("backend_base_url", $this->getBackendBaseUrl());
        $this->assignGlobal("image_base_url", $this->getImageBaseUrl());
        if ($this->moduleVisual) {
            $this->assignGlobal("page_title", $this->moduleVisual->getTitle());
            $this->assignGlobal("module_styles", $this->moduleVisual->renderStyles());
            $this->assignGlobal("module_scripts", $this->moduleVisual->renderScripts());
        }
        $this->assignGlobal("backend_base_url_raw", $this->getBackendBaseUrlRaw());
        $this->assignGlobal("backend_base_url_without_tab", $this->getBackendBaseUrlWithoutTab());

        $moduleIdTextField = new TextField("module_id", "", BlackBoard::$MODULE_ID, true, false, "", false);
        $this->assignGlobal("module_id_form_field", $moduleIdTextField->render());
        $moduleTabIdTextField = new TextField("module_tab_id", "", BlackBoard::$MODULE_TAB_ID, true, false, "", false);
        $this->assignGlobal("module_tab_id_form_field", $moduleTabIdTextField->render());

        $this->assignGlobal("actions_menu", $this->getActionsMenu()->render());
        $this->assignGlobal("website_title", $this->settings->getWebsiteTitle());
        $this->assignGlobal("navigation_menu", $navigation_menu->render());
        $this->assignGlobal("current_user_indicator", $currentUserIndicator->render());
        $this->assignGlobal("notification_bar", $notification_bar->render());
        $this->assignGlobal("content_pane", $this->renderContentPane());
        $this->assignGlobal("tab_menu", $this->renderTabMenu());
        $this->assignGlobal("system_version", SYSTEM_VERSION);
        $this->assignGlobal("db_version", $this->settings->getDatabaseVersion());

        if (Logs::hasLogs()) {
            $system_logs = new WarningMessage(Logs::asString());
            $this->assign('system_logs', $system_logs->render());
        }
    }

    private function getActionsMenu(): ActionsMenu {
        $action_buttons = array();
        if ($this->moduleVisual) {
            $action_buttons = $this->moduleVisual->getActionButtons();
        }
        return new ActionsMenu($action_buttons);
    }

    private function renderContentPane(): string {
        if (!is_null($this->moduleVisual)) {
            return $this->moduleVisual->render();
        } else {
            return $this->getTemplateEngine()->fetch("home_wrapper.tpl");
        }
    }

    private function renderTabMenu(): string {
        if (!is_null($this->moduleVisual)) {
            $tabMenu = new TabMenu();
            $activeTabId = $this->moduleVisual->loadTabMenu($tabMenu);
            $tabMenu->setCurrentTabId($activeTabId);
            return $tabMenu->render();

        }
        return "";
    }

}