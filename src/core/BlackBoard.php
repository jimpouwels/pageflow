<?php

namespace Pageflow\Core\core;

use const Pageflow\CMS_ROOT;

class BlackBoard {

    static ?int $MODULE_ID = null;
    static int $MODULE_TAB_ID = 0;

    private function __construct() {}

    public static function getBackendBaseUrl(): string {
        $baseUrl = sprintf("/admin?module_id=%s", BlackBoard::$MODULE_ID);
        $baseUrl .= sprintf("&module_tab_id=%s", BlackBoard::$MODULE_TAB_ID);
        return $baseUrl;
    }

    public static function getBackendBaseUrlWithoutTab(): string {
        return sprintf("/admin?module_id=%s", BlackBoard::$MODULE_ID);
    }

    public static function getBackendBaseUrlRaw(): string {
        return "/admin";
    }

    public static function getImageBaseUrl(): string {
        return "/admin/image";
    }

    public static function getFunctionalImageBaseUrl(): string {
        return "/admin/fimage";
    }

    public static function getModuleFileUrl(string $moduleIdentifier, string $filePath): string {
        return '/admin?file=' . urlencode($filePath) . '&module=' . urlencode($moduleIdentifier);
    }

    public static function getElementFileUrl(string $elementIdentifier, string $filePath): string {
        return '/admin?file=' . urlencode($filePath) . '&element=' . urlencode($elementIdentifier);
    }

    public static function getModuleIconUrl(string $moduleIdentifier): string {
        $extension = file_exists(CMS_ROOT . '/modules/' . $moduleIdentifier . '/static/img/' . $moduleIdentifier . '.svg') ? 'svg' : 'png';
        return '/admin?file=/modules/' . $moduleIdentifier . '/img/' . $moduleIdentifier . '.' . $extension;
    }

}