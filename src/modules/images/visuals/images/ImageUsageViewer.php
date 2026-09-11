<?php

namespace Pageflow\Core\modules\images\visuals\images;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\core\BlackBoard;
use Pageflow\Core\database\dao\ModuleDao;
use Pageflow\Core\database\dao\ModuleDaoMysql;
use Pageflow\Core\elements\photo_album_element\dao\PhotoAlbumElementHelperDao;
use Pageflow\Core\elements\image_element\dao\ImageElementHelperDao;
use Pageflow\Core\modules\articles\service\ArticleService;
use Pageflow\Core\modules\articles\service\ArticleInteractor;
use Pageflow\Core\core\model\ElementHolderType;

class ImageUsageViewer extends Panel {

    private PhotoAlbumElementHelperDao $photoAlbumElementHelperDao;
    private ImageElementHelperDao $imageElementHelperDao;
    private ArticleService $articleService;
    private ModuleDao $moduleDao;
    private int $imageId;

    public function __construct(int $imageId) {
        parent::__construct($this->getTextResource('image_usage_viewer_title'));
        $this->photoAlbumElementHelperDao = new PhotoAlbumElementHelperDao();
        $this->imageElementHelperDao = new ImageElementHelperDao();
        $this->articleService = ArticleInteractor::getInstance();
        $this->moduleDao = ModuleDaoMysql::getInstance();
        $this->imageId = $imageId;
    }

    public function getPanelContentTemplate(): string {
        return "images/templates/images/image_usage_viewer.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $elementHolders = array();
        foreach ($this->photoAlbumElementHelperDao->getElementHoldersWhereImageIsUsed($this->imageId) as $elementHolder) {
            $elementHolders[$elementHolder->getId()] = $elementHolder;
        }
        foreach ($this->imageElementHelperDao->getElementHoldersWhereImageIsUsed($this->imageId) as $elementHolder) {
            $elementHolders[$elementHolder->getId()] = $elementHolder;
        }
        foreach ($this->articleService->getArticlesWhereImageIsUsedAsWallpaperOrLeadImage($this->imageId) as $elementHolder) {
            $elementHolders[$elementHolder->getId()] = $elementHolder;
        }
        $elementHoldersData = array_map(fn($elementHolder) => $this->buildElementHolderData($elementHolder), $elementHolders);
        $data->assign('element_holders', array_values($elementHoldersData));
        $data->assign('image_usage_viewer_empty_text', $this->getTextResource('image_usage_viewer_empty_text'));
    }

    private function buildElementHolderData(ElementHolder $elementHolder): array {
        $moduleIdentifier = '';
        $param = '';
        $type = '';
        switch ($elementHolder->getType()) {
            case ElementHolderType::PAGE:
                $type = $this->getTextResource('images_usage_viewer_page_type');
                $moduleIdentifier = 'pages';
                $param = 'page';
                break;
            case ElementHolderType::ARTICLE:
                $type = $this->getTextResource('images_usage_viewer_article_type');
                $moduleIdentifier = 'articles';
                $param = 'article';
                break;
            case ElementHolderType::BLOCK:
                $type = $this->getTextResource('images_usage_viewer_block_type');
                $moduleIdentifier = 'blocks';
                $param = 'block';
                break;
        }
        return array(
            'id' => $elementHolder->getId(),
            'name' => $elementHolder->getName(),
            'type' => $type,
            'url' => $this->getBackendUrlForElementHolder($moduleIdentifier, $param, $elementHolder->getId()),
            'title' => $elementHolder->getTitle()
        );
    }

    private function getBackendUrlForElementHolder(string $moduleIdentifier, string $param, int $elementHolderId): string {
        $module = $this->moduleDao->getModuleByIdentifier($moduleIdentifier);
        if (!$module) {
            return "";
        }
        return BlackBoard::getBackendBaseUrlRaw() . "?module_id=" . $module->getId() . "&" . $param . "=" . $elementHolderId;
    }
}
