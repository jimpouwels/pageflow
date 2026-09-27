<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformItem;

class FormButtonVisual extends FormItemVisual {

    public function __construct(Page $page, ?Article $article, Webform $webform, WebformItem $webformItem) {
        parent::__construct($page, $article, $webform, $webformItem);
    }

    public function getFormItemTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    public function loadFormItem(): void {}

}