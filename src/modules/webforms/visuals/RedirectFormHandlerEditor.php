<?php

namespace Pageflow\Core\modules\webforms\visuals;

use Pageflow\Core\modules\pages\service\PageInteractor;
use Pageflow\Core\modules\pages\service\PageService;
use Pageflow\Core\modules\webforms\model\WebformHandlerProperty;
use Pageflow\Core\view\views\PageLookup;
use Pageflow\Core\view\views\Visual;

class RedirectFormHandlerEditor extends Visual {

    private ?WebformHandlerProperty $property = null;
    private PageService $pageService;

    public function __construct() {
        parent::__construct();
        $this->pageService = PageInteractor::getInstance();
    }

    public function getTemplateFilename(): string {
        return 'webforms/templates/webforms/redirect_form_handler_editor.tpl';
    }

    public function load(): void {
        $id = $this->property->getId();

        $selectedValue = $this->property->getValue();
        $pageLookup = new PageLookup(
            "handler_property_{$id}_field",
            'webforms_redirect_handler_page_picker',
            $selectedValue,
            'webforms_redirect_handler_page_picker',
            'article_editor_select_parent_article_label',
            true,
            null,
            null,
            'update_webform'
        );
        $this->assign('page_lookup', $pageLookup->render());
    }

    public function setCurrentValue(WebformHandlerProperty $property): void {
        $this->property = $property;
    }

}