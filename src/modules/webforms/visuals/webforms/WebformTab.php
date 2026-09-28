<?php

namespace Pageflow\Core\modules\webforms\visuals\webforms;

use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\WebformRequestHandler;
use Pageflow\Core\view\views\Visual;
use const Pageflow\Core\ACTION_FORM_ID;

class WebformTab extends Visual {

    private ?WebForm $currentWebform;

    public function __construct(WebformRequestHandler $webformRequestHandler) {
        parent::__construct();
        $this->currentWebform = $webformRequestHandler->getCurrentWebForm();
    }

    public function getTemplateFilename(): string {
        return "webforms/templates/webforms/root.tpl";
    }

    public function load(): void {
        $this->assign("action_form_id", ACTION_FORM_ID);
        $this->assign('list', $this->renderWebFormsList());
        if ($this->currentWebform) {
            $this->assign('id', $this->currentWebform->getId());
            $this->assign('metadata_editor', $this->renderMetadataEditor());
            $this->assign('webform_editor', $this->renderWebFormEditor());
            $this->assign('handlers_editor', $this->renderHandlersEditor());
        }
    }

    private function renderWebFormsList(): string {
        $webformList = new WebformList($this->currentWebform);
        return $webformList->render();
    }

    private function renderMetadataEditor(): string {
        $metadataEditor = new WebformMetadataEditor($this->currentWebform);
        return $metadataEditor->render();
    }

    private function renderWebFormEditor(): string {
        $webformEditor = new WebformEditor($this->currentWebform);
        return $webformEditor->render();
    }

    private function renderHandlersEditor(): string {
        $handlersEditor = new HandlersEditor($this->currentWebform);
        return $handlersEditor->render();
    }
}