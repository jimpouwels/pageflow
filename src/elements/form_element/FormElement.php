<?php

namespace Pageflow\Core\elements\form_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\form_element\dao\FormElementMetadataDao;
use Pageflow\Core\elements\form_element\visuals\FormElementEditor;
use Pageflow\Core\elements\form_element\visuals\FormElementStatics;
use Pageflow\Core\frontend\ElementFrontendVisual;
use Pageflow\Core\frontend\FormElementFrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class FormElement extends Element {

    private ?WebForm $webform = null;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new FormElementMetadataDao($this));
    }

    public function setWebForm(?WebForm $webform): void {
        $this->webform = $webform;
    }

    public function getWebForm(): ?WebForm {
        return $this->webform;
    }

    public function getStatics(): Visual {
        return new FormElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new FormElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): ElementFrontendVisual {
        return new FormElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new FormElementRequestHandler($this);
    }

    public function getSummaryText(): string {
        $summaryText = "";
        if ($this->webform) {
            $summaryText = $this->webform->getTitle();
        }
        return $summaryText;
    }
}

