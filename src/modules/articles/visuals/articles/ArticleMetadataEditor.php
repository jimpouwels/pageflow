<?php

namespace Pageflow\Core\modules\articles\visuals\articles;

use Pageflow\Core\database\dao\ArticleDao;
use Pageflow\Core\database\dao\ArticleDaoMysql;
use Pageflow\Core\database\dao\WebformDao;
use Pageflow\Core\database\dao\WebformDaoMysql;
use Pageflow\Core\friendly_urls\FriendlyUrlManager;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\service\PageInteractor;
use Pageflow\Core\modules\pages\service\PageService;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\ArticleLookup;
use Pageflow\Core\view\views\DateField;
use Pageflow\Core\view\views\ImageLookup;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\Pulldown;
use Pageflow\Core\view\views\ReadonlyTextField;
use Pageflow\Core\view\views\SingleCheckbox;
use Pageflow\Core\view\views\TemplatePicker;
use Pageflow\Core\view\views\TextArea;
use Pageflow\Core\view\views\TextField;
use const Pageflow\Core\ACTION_FORM_ID;
use const Pageflow\Core\ADD_ELEMENT_FORM_ID;
use const Pageflow\Core\DELETE_ELEMENT_FORM_ID;
use const Pageflow\Core\EDIT_ELEMENT_HOLDER_ID;
use const Pageflow\Core\ELEMENT_HOLDER_FORM_ID;

class ArticleMetadataEditor extends Panel {

    private Article $currentArticle;
    private ArticleDao $articleDao;
    private WebformDao $webformDao;
    private PageService $pageService;
    private FriendlyUrlManager $friendlyUrlManager;

    public function __construct(Article $currentArticle) {
        parent::__construct('Algemeen', 'article_metadata_editor');
        $this->currentArticle = $currentArticle;
        $this->articleDao = ArticleDaoMysql::getInstance();
        $this->webformDao = WebformDaoMysql::getInstance();
        $this->pageService = PageInteractor::getInstance();
        $this->friendlyUrlManager = FriendlyUrlManager::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "articles/templates/articles/metadata.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $nameField = new TextField("name", $this->getTextResource('article_editor_name_label'), $this->currentArticle->getName(), true, false, null);
        $titleField = new TextField("title", $this->getTextResource('article_editor_title_label'), $this->currentArticle->getTitle(), true, false, null);
        $seoTitleField = new TextField("seo_title", $this->getTextResource('article_editor_seo_title_label'), $this->currentArticle->getSeoTitle(), false, false, null);
        $templatePickerField = new TemplatePicker("template", $this->getTextResource("article_editor_template_field"), $this->currentArticle->getTemplateId(), false, "", $this->currentArticle->getScope());
        $urlTitleField = new TextField('url_title', $this->getTextResource('article_editor_url_title_field'), $this->currentArticle->getUrlTitle(), false, false, "");

        $url = "";
        if ($this->currentArticle->getTargetPageId()) {
            $url = $this->friendlyUrlManager->getFriendlyUrlForElementHolder($this->pageService->getPageById($this->currentArticle->getTargetPageId()));
        }
        $url .= $this->friendlyUrlManager->getFriendlyUrlForElementHolder($this->currentArticle);
        $urlField = new ReadonlyTextField('friendly_url', $this->getTextResource('friendly_url_label'), $url, '');

        $descriptionField = new TextArea("article_description", $this->getTextResource('article_editor_description_label'), $this->currentArticle->getDescription(), false, true, null);
        $publishedField = new SingleCheckbox("article_published", $this->getTextResource('article_editor_published_label'), $this->currentArticle->isPublished(), false, "");
        $publicationDateField = new DateField("publication_date", $this->getTextResource('article_editor_publication_date_label'), $this->getDateValue($this->currentArticle->getPublicationDate()), true, null);
        $sortDateField = new DateField("sort_date", $this->getTextResource('article_editor_sort_date_label'), $this->getDateValue($this->currentArticle->getSortDate()), true, null);
        $targetPagesField = new Pulldown("article_target_page", $this->getTextResource('article_editor_target_page_label'), $this->currentArticle->getTargetPageId(), $this->getTargetPageOptions(), false, null, true);
        $commentFormsField = new Pulldown("article_comment_webform", $this->getTextResource('article_editor_comment_webform_label'), $this->currentArticle->getCommentWebFormId(), $this->getWebFormsOptions(), false, null, true);
        $parentArticleLookup = new ArticleLookup(
            "parent_article_id",
            'article_editor_parent_article_label',
            $this->currentArticle->getParentArticleId(),
            'delete_parent_article_id',
            $this->currentArticle->getId()
        );
        $imagePickerField = new ImageLookup(
            "article_image_ref_" . $this->currentArticle->getId(), 
            $this->getTextResource('article_editor_image_label'), 
            $this->currentArticle->getImageId(), 
            $this->currentArticle->getId() . "_image",  // Unique context ID for UI
            null, 
            false,
            "/admin/api/article/image?id=" . $this->currentArticle->getId(),
            "/admin/api/article/update_image",
            "/admin/api/article/delete_image",
            $this->currentArticle->getId()  // Entity ID for REST calls
        );
        $wallpaperPickerField = new ImageLookup(
            "article_wallpaper_ref_" . $this->currentArticle->getId(), 
            $this->getTextResource('article_editor_wallpaper_label'), 
            $this->currentArticle->getWallpaperId(), 
            $this->currentArticle->getId() . "_wallpaper",  // Unique context ID for UI
            null, 
            false,
            "/admin/api/article/wallpaper?id=" . $this->currentArticle->getId(),
            "/admin/api/article/update_wallpaper",
            "/admin/api/article/delete_wallpaper",
            $this->currentArticle->getId()  // Entity ID for REST calls
        );

        $data->assign("child_articles", $this->renderChildArticles());
        $data->assign("current_article_id", $this->currentArticle->getId());
        $data->assign("name_field", $nameField->render());
        $data->assign("title_field", $titleField->render());
        $data->assign("seo_title_field", $seoTitleField->render());
        $data->assign('template_field', $templatePickerField->render());
        $data->assign('url_field', $urlField->render());
        $data->assign('url', $url);
        $data->assign('url_title_field', $urlTitleField->render());
        $data->assign("description_field", $descriptionField->render());
        $data->assign("published_field", $publishedField->render());
        $data->assign("publication_date_field", $publicationDateField->render());
        $data->assign("sort_date_field", $sortDateField->render());
        $data->assign("target_pages_field", $targetPagesField->render());
        $data->assign("parent_article_field", $parentArticleLookup->render());
        $data->assign("comment_forms_field", $commentFormsField->render());
        $data->assign("wallpaper_picker_field", $wallpaperPickerField->render());
        $data->assign("image_picker_field", $imagePickerField->render());
        $this->assignElementHolderFormIds($data);

        // metadata fields
        $metadataFields = array();
        foreach ($this->articleDao->getMetadataFields() as $metadataField) {
            $fieldValue = $this->articleDao->getMetadataFieldValue($this->currentArticle, $metadataField);
            $value = $fieldValue ? $fieldValue->getValue() : "";
            $metadataField = new TextField('metadata_field_' . $metadataField->getId(), $metadataField->getName(), $value, false, false, "");
            $metadataFields[] = $metadataField->render();
        }
        $data->assign("metadata_fields", $metadataFields);
    }

    private function renderChildArticles(): array {
        $childArticlesData = array();
        $childArticles = $this->articleDao->getAllChildArticles($this->currentArticle->getId());
        foreach ($childArticles as $childArticle) {
            $childArticleData = array();
            $childArticleData['id'] = $childArticle->getId();
            $childArticleData['title'] = $childArticle->getTitle();
            $childArticleData['url'] = "{$this->getBackendBaseUrl()}&article={$childArticle->getId()}";
            $childArticlesData[] = $childArticleData;
        }
        return $childArticlesData;
    }

    private function getDateValue(string $date): string {
        return substr($date, 0, 10);
    }

    private function getTargetPageOptions(): array {
        $targetPageOption = array();

        $allTargetPages = $this->articleDao->getTargetPages();
        foreach ($allTargetPages as $articleTargetPage) {
            $targetPageOption[] = array("name" => $articleTargetPage->getTitle(), "value" => $articleTargetPage->getId());
        }
        return $targetPageOption;
    }

    private function getWebFormsOptions(): array {
        $webformOptions = array();

        $allWebforms = $this->webformDao->getAllWebForms();
        foreach ($allWebforms as $webform) {
            $webformOptions[] = array("name" => $webform->getTitle(), "value" => $webform->getId());
        }
        return $webformOptions;
    }

    private function assignElementHolderFormIds(TemplateData $data): void {
        $data->assign("add_element_form_id", ADD_ELEMENT_FORM_ID);
        $data->assign("edit_element_holder_id", EDIT_ELEMENT_HOLDER_ID);
        $data->assign("element_holder_version", $this->currentArticle->getVersion());
        $data->assign("element_holder_form_id", ELEMENT_HOLDER_FORM_ID);
        $data->assign("action_form_id", ACTION_FORM_ID);
        $data->assign("delete_element_form_id", DELETE_ELEMENT_FORM_ID);
    }

}