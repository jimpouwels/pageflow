<?php

namespace Pageflow\Core\modules\webforms\handlers;

use Pageflow\Core\database\dao\SettingsDao;
use Pageflow\Core\database\dao\SettingsDaoMysql;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;

class EmailFormHandler extends FormHandler {

    public static string $TYPE = 'email_form_handler';
    private SettingsDao $settingsDao;

    public function __construct() {
        parent::__construct();
        $this->settingsDao = SettingsDaoMysql::getInstance();
    }

    public function getRequiredProperties(): array {
        return array(
            new HandlerProperty('target_email_address', 'textfield'),
            new HandlerProperty('subject', 'textfield'),
            new HandlerProperty('template', 'textarea')
        );
    }

    public function getNameResourceIdentifier(): string {
        return 'webforms_email_form_handler_name';
    }

    public function getType(): string {
        return self::$TYPE;
    }

    public function handle(array $fields, Page $page, ?Article $article): void {
        $message = $this->getFilledInPropertyValue('template');
        $subject = $this->getFilledInPropertyValue('subject');
        $targetEmailAddress = $this->settingsDao->getSettings()->getEmailAddress();
        if (!$targetEmailAddress) {
            $targetEmailAddress = $this->getProperty('target_email_address');
        }
        $headers = array(
            'From' => $targetEmailAddress
        );
        mail($targetEmailAddress, $subject, $message, $headers);
    }
}