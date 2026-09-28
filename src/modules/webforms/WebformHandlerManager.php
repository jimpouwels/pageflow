<?php

namespace Pageflow\Core\modules\webforms;

use Pageflow\Core\modules\webforms\handlers\ArticleCommentFormHandler;
use Pageflow\Core\modules\webforms\handlers\EmailFormHandler;
use Pageflow\Core\modules\webforms\handlers\FormHandler;
use Pageflow\Core\modules\webforms\handlers\RedirectFormHandler;

class WebformHandlerManager {

    private array $allHandlers = array();
    private static ?WebformHandlerManager $instance = null;

    private function __construct() {
        $this->allHandlers[] = new EmailFormHandler();
        $this->allHandlers[] = new RedirectFormHandler();
        $this->allHandlers[] = new ArticleCommentFormHandler();
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new WebformHandlerManager();
        }
        return self::$instance;
    }

    public function getHandler(string $type): FormHandler {
        $foundHandler = null;
        foreach ($this->allHandlers as $handler) {
            if ($handler->getType() == $type) {
                $foundHandler = $handler;
            }
        }
        return $foundHandler;
    }

    public function getAllHandlers(): array {
        return $this->allHandlers;
    }
}