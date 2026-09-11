<?php

namespace Pageflow\Core\modules\images\visuals\images;

use Pageflow\Core\modules\images\model\Image;
use Pageflow\Core\utilities\ImageUtility;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Button;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\ReadonlyTextField;
use Pageflow\Core\view\views\TextField;

class ImageMobileEditor extends Panel {

    private Image $currentImage;

    public function __construct(Image $current) {
        parent::__construct('Mobile Editor');
        $this->currentImage = $current;
    }

    public function getPanelContentTemplate(): string {
        return "images/templates/images/image_editor.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $this->assignImageMetaDataFields($data);
    }


    private function assignImageMetaDataFields(TemplateData $data): void {
        $sizeField = null;
        $newWidthField = null;
        $newHeightField = null;
        $cropTopField = null;
        $cropBottomField = null;
        $cropLeftField = null;
        $cropRightField = null;
        $cropHorizontalField = null;
        $cropVerticalField = null;
        $url = null;
        $resetButton = null;
        if (ImageUtility::exists($this->currentImage->getMobileFilename())) {
            $imageObj = ImageUtility::loadImage($this->currentImage->getMobileFilename());
            $sizeField = new ReadonlyTextField("image_mobile_size", $this->getTextResource("image_editor_size_label"), $imageObj->getImageWidth() . ' x ' . $imageObj->getImageHeight(), "image-size-display");
            $newWidthField = new TextField("image_mobile_new_width", $this->getTextResource('image_editor_new_width_label'), "", false, false, "");
            $newHeightField = new TextField("image_mobile_new_height", $this->getTextResource('image_editor_new_height_label'), "", false, false, "");
            $cropTopField = new TextField("image_mobile_crop_top", $this->getTextResource('image_editor_crop_top'), 0, false, false, "");
            $cropBottomField = new TextField("image_mobile_crop_bottom", $this->getTextResource('image_editor_crop_bottom'), 0, false, false, "");
            $cropLeftField = new TextField("image_mobile_crop_left", $this->getTextResource('image_editor_crop_left'), 0, false, false, "");
            $cropRightField = new TextField("image_mobile_crop_right", $this->getTextResource('image_editor_crop_right'), 0, false, false, "");
            $cropHorizontalField = new TextField("image_mobile_crop_horizontal", $this->getTextResource('image_editor_crop_horizontal_center'), 0, false, false, "");
            $cropVerticalField = new TextField("image_mobile_crop_vertical", $this->getTextResource('image_editor_crop_vertical_center'), 0, false, false, "");
            $url = $this->currentImage->getMobileUrl();
            $resetButton = new Button("image_mobile_reset", "image_mobile_copy_from_desktop", null);
        }
        $data->assignVisual("size_field", $sizeField);
        $data->assignVisual("new_width_field", $newWidthField);
        $data->assignVisual("new_height_field", $newHeightField);
        $data->assignVisual("crop_top_field", $cropTopField);
        $data->assignVisual("crop_bottom_field", $cropBottomField);
        $data->assignVisual("crop_left_field", $cropLeftField);
        $data->assignVisual("crop_right_field", $cropRightField);
        $data->assignVisual("crop_horizontal_field", $cropHorizontalField);
        $data->assignVisual("crop_vertical_field", $cropVerticalField);
        $data->assign("crop_field_prefix", "image_mobile_crop_");
        $data->assign("url", $url);
        $data->assign("title", $this->currentImage->getTitle());
        $data->assignVisual("reset_button", $resetButton);
    }
}
