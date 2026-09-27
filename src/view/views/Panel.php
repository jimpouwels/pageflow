<?php

namespace Pageflow\Core\view\views;

use Pageflow\Core\view\TemplateData;

abstract class Panel extends Visual {

    private string $titleResourceIdentifier;
    private string $class;

    public function __construct(string $titleResourceIdentifier, string $class = "") {
        parent::__construct();
        $this->titleResourceIdentifier = $titleResourceIdentifier;
        $this->class = $class;
    }

    public function getTemplateFilename(): string {
        return "panel.tpl";
    }

    abstract function getPanelContentTemplate(): string;

    abstract function loadPanelContent(TemplateData $data): void;

    public function load(): void {
        $panelContentTemplateData = $this->createChildData();
        $this->loadPanelContent($panelContentTemplateData);

        $this->assign('content', $this->fetch($this->getPanelContentTemplate(), $panelContentTemplateData));
        $this->assign('title_resource_identifier', $this->titleResourceIdentifier);
        $this->assign('class', $this->class);
    }

}
