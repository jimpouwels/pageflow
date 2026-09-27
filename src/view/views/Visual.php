<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\authentication\Session;
use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\TemplateEngine;
use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;

abstract class Visual {

    private TemplateEngine $templateEngine;
    private TemplateData $templateData;
    private TemplateService $templateService;

    public function __construct(?Visual $parent = null) {
        $this->templateEngine = TemplateEngine::getInstance();
        $this->templateData = $this->templateEngine->createChildData();
        $this->templateService = TemplateInteractor::getInstance();

    }

    protected function getTemplateService(): TemplateService {
        return $this->templateService;
    }

    public function render(): string {
        $this->load();
        return $this->templateEngine->fetch($this->getTemplateFilename(), $this->templateData);
    }

    abstract function load(): void;

    abstract function getTemplateFilename(): string;

    protected function getTemplateEngine(): TemplateEngine {
        return $this->templateEngine;
    }

    protected function assign(string $key, mixed $value): void {
        $this->templateData->assign($key, $value);
    }

    protected function assignGlobal(string $key, mixed $value): void {
        $this->templateEngine->assign($key, $value);
    }

    protected function createChildData(): TemplateData {
        return $this->templateEngine->createChildData();
    }

    protected function fetch(string $template, TemplateData $data): string {
        return $this->templateEngine->fetch($template, $data);
    }

    protected function getTextResource(string $identifier): string {
        return Session::getTextResource($identifier);
    }

    protected function getBackendBaseUrl(): string {
        return BlackBoard::getBackendBaseUrl();
    }

    protected function getImageBaseUrl(): string {
        return BlackBoard::getImageBaseUrl();
    }

    protected function getBackendBaseUrlRaw(): string {
        return BlackBoard::getBackendBaseUrlRaw();
    }

    protected function getBackendBaseUrlWithoutTab(): string {
        return BlackBoard::getBackendBaseUrlWithoutTab();
    }

    protected function getCurrentTabId(): int {
        return BlackBoard::$MODULE_TAB_ID;
    }
}