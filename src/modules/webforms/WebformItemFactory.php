<?php

namespace Pageflow\Core\modules\webforms;

use Pageflow\Core\frontend\FormItemVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\webforms\form\WebformItemForm;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformButton;
use Pageflow\Core\modules\webforms\model\WebformDropdown;
use Pageflow\Core\modules\webforms\model\WebformItem;
use Pageflow\Core\modules\webforms\model\WebformTextArea;
use Pageflow\Core\modules\webforms\model\WebformTextfield;
use Pageflow\Core\modules\webforms\visuals\webforms\fields\WebformItemVisual;

class WebformItemFactory {

    private array $types = array();
    private static ?WebformItemFactory $instance = null;

    private function __construct() {
        $this->addType(WebformTextfield::$TYPE, "WebformTextfieldVisual", "WebformTextfieldForm", "FormTextfieldVisual");
        $this->addType(WebFormTextArea::$TYPE, "WebformTextareaVisual", "WebformTextAreaForm", "FormTextAreaVisual");
        $this->addType(WebformDropdown::$TYPE, "WebformDropDownVisual", "WebformDropDownForm", "FormDropDownVisual");
        $this->addType(WebformButton::$TYPE, "WebformButtonVisual", "WebformButtonForm", "FormButtonVisual");
    }

    public static function getInstance(): WebformItemFactory {
        if (!self::$instance) {
            self::$instance = new WebformItemFactory();
        }
        return self::$instance;
    }

    public function getBackendVisualFor(WebformItem $webform_item): WebformItemVisual {
        $className = "Pageflow\\Core\\modules\\webforms\\visuals\\webforms\\fields\\" . $this->getFormItemType($webform_item->getType())->getBackendVisualClassname();
        return new $className($webform_item);
    }

    public function getBackendFormFor(WebformItem $webform_item): WebformItemForm {
        $className = "Pageflow\\Core\\modules\\webforms\\form\\" . $this->getFormItemType($webform_item->getType())->getBackendFormClassname();
        return new $className($webform_item);
    }

    public function getFrontendVisualFor(WebForm $webform, WebformItem $webform_item, Page $page, ?Article $article): FormItemVisual {
        $className = "Pageflow\\Core\\frontend\\" . $this->getFormItemType($webform_item->getType())->getFrontendVisualClassname();
        return new $className($page, $article, $webform, $webform_item);
    }

    private function getFormItemType(string $typeToFind): FormItemType {
        $foundType = null;
        foreach ($this->types as $type) {
            if ($type->getTypeName() == $typeToFind) {
                $foundType = $type;
            }
        }
        return $foundType;
    }

    private function addType(string $typeName, string $backendVisualClassname, string $backendFormClassname, string $frontendVisualClassname): void {
        $this->types[] = new FormItemType($typeName, $backendVisualClassname, $backendFormClassname, $frontendVisualClassname);
    }
}