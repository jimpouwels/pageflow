<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\frontend\helper\LinkHelper;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\service\ArticleInteractor;
use Pageflow\Core\modules\articles\service\ArticleService;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\modules\templates\model\TemplateVar;
use Pageflow\Core\modules\templates\service\TemplateInteractor;
use Pageflow\Core\modules\templates\service\TemplateService;
use Pageflow\Core\frontend\helper\FontStyleHelper;
use Pageflow\Core\utilities\Arrays;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\TemplateEngine;
use const Pageflow\Core\FRONTEND_TEMPLATE_DIR;

abstract class FrontendVisual {

    private TemplateEngine $templateEngine;
    private TemplateData $templateData;
    private TemplateService $templateService;
    private LinkHelper $linkHelper;
    private ?Page $page;
    private ?Article $article;
    private ?Block $block;
    private ElementDao $elementDao;
    private ArticleService $articleService;

    public function __construct(?Page $page, ?Article $article, ?Block $block = null) {
        $this->linkHelper = LinkHelper::getInstance($page, $article);
        $this->page = $page;
        $this->article = $article;
        $this->block = $block;
        $this->templateEngine = TemplateEngine::getInstance();
        $this->templateData = $this->createChildData();
        $this->templateService = TemplateInteractor::getInstance();
        $this->elementDao = ElementDaoMysql::getInstance();
        $this->articleService = ArticleInteractor::getInstance();
    }

    public function render(array &$parentData = array()): string {
        $this->load($parentData);
        $html = $this->templateEngine->fetch($this->getTemplateFilename(), $this->templateData);
        return $this->replaceTemplateIncludes($html);
    }

    public function load(array &$parentData): void {
        $presentable = $this->getPresentable();
        $templateVars = array();

        if ($presentable && $presentable->getTemplate()) {
            $templateVarDefs = $this->templateService->getTemplateVarDefsByTemplate($presentable->getTemplate());
            foreach ($presentable->getTemplate()->getTemplateVars() as $templateVar) {
                $varValue = $templateVar->getValue() ?: $this->getDefaultValueFor($templateVar, $templateVarDefs);
                if (is_numeric($varValue)) {
                    $varValue = intval($varValue);
                }
                $templateVars[$templateVar->getName()] = $varValue;
            }
        }
        $this->assign("var", $templateVars);
        $this->loadVisual($parentData);
        if ($parentData) {
            foreach ($parentData as $key => $value) {
                $this->assign($key, $value);
            }
        }
    }

    abstract function loadVisual(array &$data): void;

    abstract function getPresentable(): ?Presentable;

    abstract function getTemplateFilename(): string;

    protected function getTemplateEngine(): TemplateEngine {
        return $this->templateEngine;
    }

    protected function getLinkHelper(): LinkHelper {
        return $this->linkHelper;
    }

    protected function renderElementHolderContent(ElementHolder $elementHolder, ?array &$data): void {
        $elementGroups = array();
        $elementGroup = array();
        $previousSeparator = null;
        $pendingSeparatorOpen = null;
        $currentGroupHtmlId = null;

        foreach ($elementHolder->getElements() as $element) {
            $elementType = $this->elementDao->getElementTypeForElement($element->getId())->getIdentifier();
            
            if ($elementType == 'separator_element') {
                // Close current group by appending previous separator's closing to last element
                if ($previousSeparator && $previousSeparator->getTemplate() && count($elementGroup) > 0) {
                    $closePrevious = array();
                    $closePrevious['is_closing'] = true;
                    $previousVisual = $previousSeparator->getFrontendVisual($this->getPage(), $this->getArticle(), $this->getBlock());
                    $closingHtml = $previousVisual->render($closePrevious);
                    
                    $lastIndex = count($elementGroup) - 1;
                    $elementGroup[$lastIndex]["to_string"] .= $closingHtml;
                }
                
                // Save the completed group with html_id
                if (count($elementGroup) > 0) {
                    $groupData = array();
                    $groupData['html_id'] = $currentGroupHtmlId;
                    $groupData['elements'] = $elementGroup;
                    $elementGroups[] = $groupData;
                    $elementGroup = array();
                }
                
                // Store the new separator to open the next group
                if ($element->getTemplate()) {
                    $elementData = array();
                    $elementData["is_closing"] = false;
                    $elementVisual = $element->getFrontendVisual($this->getPage(), $this->getArticle(), $this->getBlock());
                    $pendingSeparatorOpen = $elementVisual->render($elementData);
                }
                
                $currentGroupHtmlId = $element->getHtmlId();
                $previousSeparator = $element;
                continue;
            }
            
            // Regular element
            $elementData = array();
            $elementData["type"] = $elementType;
            $elementData["template"] = $element->getTemplate()?->getName();
            
            if ($element->getTemplate()) {
                $elementVisual = $element->getFrontendVisual($this->getPage(), $this->getArticle(), $this->getBlock());
                $elementData["to_string"] = $elementVisual->render($elementData);
                
                // Prepend pending separator opening HTML to FIRST element in group
                if (count($elementGroup) == 0 && $pendingSeparatorOpen) {
                    $elementData["to_string"] = $pendingSeparatorOpen . $elementData["to_string"];
                    $pendingSeparatorOpen = null;
                }
            } else {
                $elementData["to_string"] = "";
            }
            
            $elementGroup[] = $elementData;
        }
        
        // Append closing separator to LAST element in final group
        if (count($elementGroup) > 0) {
            if ($previousSeparator && $previousSeparator->getTemplate()) {
                $separatorData = array();
                $separatorData['is_closing'] = true;
                $previousVisual = $previousSeparator->getFrontendVisual($this->getPage(), $this->getArticle(), $this->getBlock());
                $closingHtml = $previousVisual->render($separatorData);
                
                $lastIndex = count($elementGroup) - 1;
                $elementGroup[$lastIndex]["to_string"] .= $closingHtml;
            }
            $groupData = array();
            $groupData['html_id'] = $currentGroupHtmlId;
            $groupData['elements'] = $elementGroup;
            $elementGroups[] = $groupData;
        }
        
        $data['element_groups'] = $elementGroups;
    }

    protected function createChildData(): TemplateData {
        return $this->templateEngine->createChildData();
    }

    protected function assign(string $key, mixed $value): void {
        $this->templateData->assign($key, $value);
    }

    protected function assignGlobal(string $key, mixed $value): void {
        $this->templateEngine->assign($key, $value);
    }

    protected function fetch(string $templateFilename, ?TemplateData $templateData = null): string {
        return $this->templateEngine->fetch($templateFilename, $templateData == null ? $this->templateData : $templateData);
    }

    protected function toHtml(?string $value): string {
        if (!$value) {
            return "";
        }
        
        $value = $this->replaceSmartyQuery($value);
        $value = $this->replaceArticleMetadataReferences($value);
        $value = $this->linkHelper->processRichTextLinks($value);
        $value = FontStyleHelper::createColors($value);
        
        $value = nl2br($value);
        return $value;
    }

    protected function getPage(): Page {
        return $this->page;
    }

    protected function getBlock(): ?Block {
        return $this->block;
    }

    protected function getArticle(): ?Article {
        return $this->article;
    }

    protected function getElementHolder(): ElementHolder {
        if ($this->block) {
            return $this->block;
        }
        return !is_null($this->article) ? $this->article : $this->page;
    }

    protected function toAnchorValue(string $value): string {
        $anchorValue = strtolower($value);
        $anchorValue = str_replace("-", " ", $anchorValue);
        $anchorValue = str_replace("  ", " ", $anchorValue);
        $anchorValue = str_replace(" ", "-", $anchorValue);
        $anchorValue = str_replace("--", "-", $anchorValue);
        return urlencode($anchorValue);
    }

    private function replaceTemplateIncludes(string $html): string {
        $matches = null;
        preg_match_all('/<include template="(.*)".*\/>/', $html, $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $templateFile = $matches[1][$i];
            $templateContents = file_get_contents(FRONTEND_TEMPLATE_DIR . "/" . $templateFile);
            if (str_ends_with($templateFile, '.js')) {
                $templateContents = '{literal}' . $templateContents . '{/literal}';
            }
            $includeHtml = $this->templateEngine->fetch('string:' . $templateContents, $this->templateData);
            $html = str_replace($matches[0][$i], $includeHtml, $html);
            $html = $this->replaceTemplateIncludes($html);
        }
        return $html;
    }

    private function getDefaultValueFor(TemplateVar $templateVar, array $templateVarDefs): string {
        $value = Arrays::firstMatch($templateVarDefs, fn($item) => $templateVar->getName() == $item->getName())->getDefaultValue();
        return $value ?: "";
    }

    private function replaceSmartyQuery(string $text): string {
        $matches = array();
        preg_match_all('/\{\$[^ ]*?}/', $text, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $placeholder = $matches[0][$i];
            $globalValueQuery = str_replace('{', '', $placeholder);
            $globalValueQuery = str_replace('}', '', $globalValueQuery);
            $globalValueQuery = str_replace('$', '', $globalValueQuery);
            $parts = explode('.', $globalValueQuery);

            $currentPos = null;
            while (count($parts) > 0) {
                $part = array_shift($parts);
                if (!$currentPos) {
                    $currentPos = $this->getTemplateEngine()->getGlobal($part);
                } else {
                    $currentPos = $currentPos[$part];
                }
            }
            if (!$currentPos) {
                continue;
            }
            $text = str_replace($placeholder, $currentPos, $text);
        }
        return $text;
    }

    private function replaceArticleMetadataReferences(string $value): string {
        if ($this->article) {
            preg_match_all('/\$([A-Za-z_.]*)/', $value, $matches);
            $metadataFields = $this->articleService->getMetadataFields();
            for ($i = 0; $i < count($matches[0]); $i++) {
                foreach ($metadataFields as $field) {
                    if ($matches[1][$i] == $field->getName()) {
                        $fieldValue = $this->articleService->getMetadataFieldValue($this->article, $field)->getValue() ?: $field->getDefaultValue();
                        $value = str_replace($matches[0][$i], $fieldValue ?? '', $value);
                    } else if ($this->isParentMetadataPlaceHolder($matches[1][$i], $field->getName())) {
                        $fieldValue = $this->articleService->getMetadataFieldValue($this->articleService->getArticle($this->article->getParentArticleId()), $field)->getValue() ?: $field->getDefaultValue();
                        $value = str_replace($matches[0][$i], $fieldValue ?? '', $value);
                    }
                }
            }
        }
        return $value;
    }

    private function isParentMetadataPlaceHolder(string $placeholder, string $fieldName): bool {
        $split = explode('.', $placeholder);
        return $split[0] == "parent_article" && $split[1] == $fieldName;
    }
}