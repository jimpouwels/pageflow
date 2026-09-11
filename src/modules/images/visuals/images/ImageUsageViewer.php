<?php

namespace Pageflow\Core\modules\images\visuals\images;

use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\database\dao\ModuleDao;
use Pageflow\Core\database\dao\ModuleDaoMysql;
use Pageflow\Core\elements\photo_album_element\dao\PhotoAlbumElementHelperDao;
use Pageflow\Core\elements\image_element\dao\ImageElementHelperDao;
use Pageflow\Core\core\model\ElementHolderType;

class ImageUsageViewer extends Panel {

    private PhotoAlbumElementHelperDao $photoAlbumElementHelperDao;
    private ImageElementHelperDao $imageElementHelperDao;
    private ModuleDao $moduleDao;
    private int $imageId;

    public function __construct(int $imageId) {
        parent::__construct($this->getTextResource('image_usage_viewer_title'));
        $this->photoAlbumElementHelperDao = new PhotoAlbumElementHelperDao();
        $this->imageElementHelperDao = new ImageElementHelperDao();
        $this->moduleDao = ModuleDaoMysql::getInstance();
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
            $moduleIdentifier = '';
            $param = '';
            switch ($elementHolder->getType()) {
                case ElementHolderType::PAGE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_page_type');
                    $moduleIdentifier = 'pages';
                    $param = 'page';
                    break;
                case ElementHolderType::ARTICLE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_article_type');
                    $moduleIdentifier = 'articles';
                    $param = 'article';
                    break;
                case ElementHolderType::BLOCK:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_block_type');
                    $moduleIdentifier = 'blocks';
                    $param = 'block';
                    break;
            }
            $elementHolderData['url'] = $this->getBackendUrlForElementHolder($moduleIdentifier, $param, $elementHolder->getId());
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
            $moduleIdentifier = '';
            $param = '';
            switch ($elementHolder->getType()) {
                case ElementHolderType::PAGE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_page_type');
                    $moduleIdentifier = 'pages';
                    $param = 'page';
                    break;
                case ElementHolderType::ARTICLE:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_article_type');
                    $moduleIdentifier = 'articles';
                    $param = 'article';
                    break;
                case ElementHolderType::BLOCK:
                    $elementHolderData['type'] = $this->getTextResource('images_usage_viewer_block_type');
                    $moduleIdentifier = 'blocks';
                    $param = 'block';
                    break;
            }
            $elementHolderData['url'] = $this->getBackendUrlForElementHolder($moduleIdentifier, $param, $elementHolder->getId());
            $elementHolderData['title'] = $elementHolder->getTitle();
            $elementHoldersData[] = $elementHolderData;
        }
    }

    private function getBackendUrlForElementHolder(string $moduleIdentifier, string $param, int $elementHolderId): string {
        $module = $this->moduleDao->getModuleByIdentifier($moduleIdentifier);
        if (!$module) {
            return "";
        }
        return BlackBoard::getBackendBaseUrlRaw() . "?module_id=" . $module->getId() . "&" . $param . "=" . $elementHolderId;
    }
}
