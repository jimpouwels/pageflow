<?php

namespace Pageflow\Core\modules\images\visuals\images;

use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\elements\photo_album_element\dao\PhotoAlbumElementHelperDao;
use Pageflow\Core\elements\image_element\dao\ImageElementHelperDao;
use Pageflow\Core\core\model\ElementHolderType;

class ImageUsageViewer extends Panel {

    private PhotoAlbumElementHelperDao $photoAlbumElementHelperDao;
    private ImageElementHelperDao $imageElementHelperDao;
    private int $imageId;

    public function __construct(int $imageId) {
        parent::__construct($this->getTextResource('image_usage_viewer_title'));
        $this->photoAlbumElementHelperDao = new PhotoAlbumElementHelperDao();
        $this->imageElementHelperDao = new ImageElementHelperDao();
        $this->imageId = $imageId;
    }

    public function getPanelContentTemplate(): string {
        return "images/templates/images/image_usage_viewer.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $elementHoldersData = array();
        $this->loadElementHolderDataWhereImageIsOnPhotoAlbumElement($elementHoldersData);
        $this->loadElementHolderDataWhereImageIsOnImageElement($elementHoldersData);
        $data->assign('element_holders', $elementHoldersData);
    }

    private function loadElementHolderDataWhereImageIsOnPhotoAlbumElement(array &$elementHoldersData): void {
        $elementHolders = $this->photoAlbumElementHelperDao->getElementHoldersWhereImageIsUsed($this->imageId);
        foreach ($elementHolders as $elementHolder) {
            $elementHolderData = array(
                'id' => $elementHolder->getId(),
                'name' => $elementHolder->getName()
            );
            switch ($elementHolder->getType()) {
                case ElementHolderType::PAGE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_page_type');
                    $elementHolderData['url'] = ""; // {backendBaseUrlRaw}?module_id=?page module id???
                    break;
                case ElementHolderType::ARTICLE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_article_type');
                    $elementHolderData['url'] = "";
                    break;
                case ElementHolderType::BLOCK:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_block_type');
                    $elementHolderData['url'] = "";
                    break;
            }
            $elementHolderData['title'] = $elementHolder->getTitle();
            $elementHoldersData[] = $elementHolderData;
        }
    }

    private function loadElementHolderDataWhereImageIsOnImageElement(array &$elementHoldersData): void {
        $elementHolders = $this->imageElementHelperDao->getElementHoldersWhereImageIsUsed($this->imageId);
        foreach ($elementHolders as $elementHolder) {
            $elementHolderData = array(
                'id' => $elementHolder->getId(),
                'name' => $elementHolder->getName()
            );
            switch ($elementHolder->getType()) {
                case ElementHolderType::PAGE:
                    $elementHolderData['type'] = 'page';
                    $elementHolderData['url'] = "";
                    break;
                case ElementHolderType::ARTICLE:
                    $elementHolderData['type'] = 'article';
                    $elementHolderData['url'] = "";
                    break;
                case ElementHolderType::BLOCK:
                    $elementHolderData['type'] = 'block';
                    $elementHolderData['url'] = "";
                    break;
            }
            $elementHolderData['title'] = $elementHolder->getTitle();
            $elementHoldersData[] = $elementHolderData;
        }
    }
}
