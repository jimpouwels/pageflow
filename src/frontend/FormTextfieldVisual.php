<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformField;

class FormTextfieldVisual extends FormFieldVisual {

    public function __construct(Page $page, ?Article $article, Webform $webform, WebformField $webformField) {
        parent::__construct($page, $article, $webform, $webformField);
    }

    public function getFormFieldTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    public function loadFormField(): void {}

}