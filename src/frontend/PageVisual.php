<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\database\dao\ArticleDao;
use Pageflow\Core\database\dao\ArticleDaoMysql;
use Pageflow\Core\database\dao\BlockDao;
use Pageflow\Core\database\dao\BlockDaoMysql;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\service\PageInteractor;
use Pageflow\Core\modules\pages\service\PageService;
use Pageflow\Core\modules\templates\model\Presentable;

class PageVisual extends FrontendVisual {
    private PageService $pageService;
    private BlockDao $blockDao;
    private ArticleDao $articleDao;

    public function __construct(Page $page, ?Article $article) {
        parent::__construct($page, $article);
        $this->pageService = PageInteractor::getInstance();
        $this->blockDao = BlockDaoMysql::getInstance();
        $this->articleDao = ArticleDaoMysql::getInstance();
    }

    public function getTemplateFilename(): string {
        return $this->getTemplateFilenameForPresentable();
    }

    public function loadVisual(?array &$data): void {
        $this->assignGlobal("page", $this->getPageContentAndMetaData($this->getPage()));
        $this->assign("crumb_path", $this->renderCrumbPath());
        $this->assign("title", $this->getPage()->getTitle());
        $this->assign("seo_title", $this->getPage()->getSeoTitle());
        if ($this->getArticle()) {
            $articleData = $this->renderArticle();
            $this->assignGlobal("article", $articleData);
            $this->assign("title", $this->getArticle()->getTitle());
            $this->assign("seo_title", $this->getArticle()->getSeoTitle());
        } else {
            $this->assign("article", null);
        }
        $this->assign("blocks", $this->renderBlocks());
        $this->assign("canonical_url", $this->getLinkHelper()->createCanonicalUrl());

        $rootPageData = array();
        $this->addPageMetaData($this->pageService->getRootPage(), $rootPageData, true, false);
        $this->assign("root_page", $rootPageData);

        $this->assign("sitewide_pages", $this->getSitewidePagesData());
    }

    public function getPresentable(): ?Presentable {
        return $this->getPage();
    }

    private function getPageContentAndMetaData(Page $page): array {
        $pageData = array();
        $this->renderElementHolderContent($page, $pageData);
        $this->addPageMetaData($page, $pageData, true, true);
        return $pageData;
    }

    private function renderChildren(Page $page): array {
        $children = array();
        foreach ($this->pageService->getSubPages($page) as $subPage) {
            if (!$subPage->isPublished()) continue;
            $child = array();
            $this->addPageMetaData($subPage, $child, false, false);
            $children[] = $child;
        }
        return $children;
    }

    private function getSitewidePagesData(): array {
        $data = array();
        $pages = $this->pageService->getSitewidePages();
        foreach ($pages as $page) {
            if (!$page->isPublished()) continue;
            $pageData = array();
            $pageData['id'] = $page->getId();
            $pageData["title"] = $page->getTitle();
            $pageData['seo_title'] = $page->getSeoTitle();
            $pageData['url'] = $this->getLinkHelper()->createPageUrl($page);
            $data[] = $pageData;
        }
        return $data;
    }

    private function addPageMetaData(Page $page, array &$pageData, bool $renderChildren, bool $renderParent): void {
        $pageData["is_current_page"] = $this->getPage()->getId() == $page->getId();
        $pageData["title"] = $page->getTitle();
        $pageData["seo_title"] = $page->getSeoTitle();
        $pageData["id"] = $page->getId();
        $pageData["url"] = $this->getLinkHelper()->createPageUrl($page);
        $pageData["is_homepage"] = $page->isHomepage();
        $pageData["navigation_title"] = $page->getNavigationTitle();
        $page_description = $page->getDescription();
        if (!is_null($this->getArticle()) && $this->getArticle()->isPublished()) {
            $page_description = $this->getArticle()->getDescription();
        }
        $pageData["description"] = $this->toHtml($page_description);
        $pageData["show_in_navigation"] = $page->getShowInNavigation();
        if ($renderParent) {
            $parentData = array();
            $parent = $this->pageService->getParent($this->getPage());
            if ($parent) {
                $this->addPageMetaData($parent, $parentData, false, false);
                $pageData["parent"] = $parentData;
            }
        }
        if ($renderChildren) {
            $pageData["children"] = $this->renderChildren($page);
        }
    }

    private function renderBlocks(): array {
        $blocks = array();
        $blocks['no_position'] = array();
        foreach ($this->blockDao->getBlocksByPage($this->getPage()) as $block) {
            if (!$block->isPublished()) continue;
            $position = $block->getPosition();
            if (!is_null($position)) {
                $positionName = $position->getName();
                if (!isset($blocks[$positionName])) {
                    $blocks[$positionName] = array();
                }
                $blocks[$positionName][] = $this->renderBlock($block);
            } else {
                $blocks["no_position"][] = $this->renderBlock($block);
            }
        }
        return $blocks;
    }

    private function renderBlock(Block $block): array {
        $blockData = array();
        $blockVisual = new BlockVisual($block, $this->getPage(), $this->getArticle());
        $blockHtml = $blockVisual->render($blockData);
        $blockData["to_string"] = $blockHtml;
        return $blockData;
    }

    private function renderArticle(): array {
        $articleData = array();
        $articleVisual = new ArticleVisual($this->getPage(), $this->getArticle());
        $articleHtml = $articleVisual->render($articleData);
        $articleData["to_string"] = $articleHtml;
        return $articleData;
    }

    private function renderCrumbPath(): array {
        $crumbPathItems = array();
        $parentArticle = null;
        if ($this->getArticle() && $this->getArticle()->getParentArticleId()) {
            $parentArticle = $this->articleDao->getArticle($this->getArticle()->getParentArticleId());
            $parents = $this->pageService->getParents($this->pageService->getPageById($parentArticle->getTargetPageId()));
        } else {
            $parents = $this->pageService->getParents($this->getPage());
        }
        for ($i = 0; $i < count($parents); $i++) {
            if (($this->getPage()->getId() == $parents[$i]->getId() && !$this->getArticle()) || !$parents[$i]->getShowInNavigation()) {
                continue;
            }
            $itemData = array();
            $itemData['url'] = $this->getLinkHelper()->createPageUrl($parents[$i]);
            $itemData['title'] = $parents[$i]->getNavigationTitle();
            $crumbPathItems[] = $itemData;
        }
        if ($parentArticle) {
            $itemData = array();
            $itemData['url'] = $this->getLinkHelper()->createArticleUrl($parentArticle);
            $itemData['title'] = $parentArticle->getTitle();
            $crumbPathItems[] = $itemData;
        }
        return $crumbPathItems;
    }

}