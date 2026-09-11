<?php

namespace Pageflow\Core\modules\images\visuals\images;

use Pageflow\Core\modules\images\model\Image;
use Pageflow\Core\view\views\Visual;

class ImageEditor extends Visual {
    private Image $currentImage;

    public function __construct(Image $current) {
        parent::__construct();
        $this->currentImage = $current;
    }

    public function getTemplateFilename(): string {
        return "images/templates/images/editor.tpl";
    }

    public function load(): void {
        $this->assignMetadataEditor();
        $this->assignDesktopEditor();
        $this->assignMobileEditor();
        $this->assignImageUsageViewer();
        $this->assign("current_image_id", $this->currentImage->getId());
    }

    private function assignMetadataEditor(): void {
        $metadataEditor = new ImageMetadataEditor($this->currentImage);
        $this->assign('metadata_editor', $metadataEditor->render());
    }

    private function assignDesktopEditor(): void {
        $desktopEditor = new ImageDesktopEditor($this->currentImage);
        $this->assign('desktop_editor', $desktopEditor->render());
    }

    private function assignMobileEditor(): void {
        $mobileEditor = new ImageMobileEditor($this->currentImage);
        $this->assign('mobile_editor', $mobileEditor->render());
    }

    private function assignImageUsageViewer(): void {
        $imageUsageViewer = new ImageUsageViewer($this->currentImage->getId());
        $this->assign('image_usage_viewer', $imageUsageViewer->render());
    }

}
