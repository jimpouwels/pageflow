<?php

namespace Pageflow\Core\elements\article_overview_element;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\database\dao\ArticleDaoMysql;
use Pageflow\Core\elements\article_overview_element\dao\ArticleOverviewElementMetadataDao;
use Pageflow\Core\elements\article_overview_element\visuals\ArticleOverviewElementEditor;
use Pageflow\Core\elements\article_overview_element\visuals\ArticleOverviewElementStatics;
use Pageflow\Core\frontend\ArticleOverviewElementFrontendVisual;
use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\articles\model\ArticleTerm;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\utilities\DateUtility;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;

class ArticleOverviewElement extends Element {

    private ?string $showFrom = null;
    private ?string $showTo = null;
    private bool $showUntilToday = false;
    private ?string $orderBy = null;
    private ?string $orderType = null;
    private bool $randomArticles = false;
    private array $terms;
    private ?int $numberOfResults = null;
    private bool $siblingsOnly = false;
    private bool $includeCurrentArticle = false;

    public function __construct(int $scopeId) {
        parent::__construct($scopeId, new ArticleOverviewElementMetadataDao($this));
        $this->terms = array();
    }

    public function setShowFrom(?string $show_from): void {
        $this->showFrom = $show_from;
    }

    public function getShowFrom(): ?string {
        return $this->showFrom;
    }

    public function setShowTo(?string $show_to): void {
        $this->showTo = $show_to;
    }

    public function getShowTo(): ?string {
        return $this->showTo;
    }

    public function setShowUntilToday(bool $show_until_today): void {
        $this->showUntilToday = $show_until_today;
    }

    public function getShowUntilToday(): bool {
        return $this->showUntilToday;
    }

    public function setNumberOfResults(?int $number_of_results): void {
        $this->numberOfResults = $number_of_results;
    }

    public function getNumberOfResults(): ?int {
        return $this->numberOfResults;
    }

    public function setOrderBy(?string $order_by): void {
        $this->orderBy = $order_by;
    }

    public function getOrderBy(): ?string {
        return $this->orderBy;
    }

    public function setOrderType(?string $order_type): void {
        $this->orderType = $order_type;
    }

    public function getOrderType(): ?string {
        return $this->orderType;
    }

    public function addTerm(ArticleTerm $term): void {
        $this->terms[] = $term;
    }

    public function removeTerm(ArticleTerm $term): void {
        if (($key = array_search($term, $this->terms, true)) !== false) {
            unset($this->terms[$key]);
        }
    }

    public function setTerms(array $terms): void {
        $this->terms = $terms;
    }

    public function getTerms(): array {
        return $this->terms;
    }
    public function setSiblingsOnly(bool $siblingsOnly): void {
        $this->siblingsOnly = $siblingsOnly;
    }

    public function getSiblingsOnly(): bool {
        return $this->siblingsOnly;
    }

    public function isRandomArticles(): bool {
        return $this->randomArticles;
    }

    public function setRandomArticles(bool $randomArticles): void {
        $this->randomArticles = $randomArticles;
    }

    public function getArticles(?int $exclude, ?int $currentArticleId, bool $published): array {
        $articleDao = ArticleDaoMysql::getInstance();
        if ($this->isRandomArticles()) {
            return $articleDao->getRandomArticles($exclude, $published, $this->numberOfResults, $this->terms);
        }
        $showTo = null;
        if ($this->showUntilToday != 1 && $this->showTo) {
            $showTo = DateUtility::mysqlDateToString($this->showTo, '-');
        }
        $showFrom = null;
        if ($this->showFrom) {
            $showFrom = DateUtility::mysqlDateToString($this->showFrom, '-');
        }
        $siblingsOnlyId = ($currentArticleId && $this->getSiblingsOnly()) ? $currentArticleId : null;
        return $articleDao->advancedSearchArticles($showFrom,
            $showTo, $this->orderBy, $this->getOrderType(), $this->terms,
            $this->numberOfResults, $siblingsOnlyId, $published, $exclude);
    }

    public function getStatics(): Visual {
        return new ArticleOverviewElementStatics();
    }

    public function getBackendVisual(): ElementVisual {
        return new ArticleOverviewElementEditor($this);
    }

    public function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual {
        return new ArticleOverviewElementFrontendVisual($page, $article, $block, $this);
    }

    public function getRequestHandler(): HttpRequestHandler {
        return new ArticleOverviewElementRequestHandler($this);
    }

    public function includeCurrentArticle(): bool {
        return $this->includeCurrentArticle;
    }

    public function setIncludeCurrentArticle(bool $includeCurrentArticle): void {
        $this->includeCurrentArticle = $includeCurrentArticle;
    }

    public function getSummaryText(): string {
        $summary_text = $this->getTitle() ?? "";
        if ($this->getTerms()) {
            $summary_text .= " (Termen:";
            foreach ($this->getTerms() as $term) {
                $summary_text .= " " . $term->getName();
            }
            $summary_text .= ")";
        }
        return $summary_text;
    }

}

