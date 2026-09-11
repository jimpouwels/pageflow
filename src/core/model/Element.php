<?php

namespace Pageflow\Core\core\model;

use Pageflow\Core\frontend\FrontendVisual;
use Pageflow\Core\database\dao\ElementMetadataDao;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\request_handlers\HttpRequestHandler;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\Visual;
use const Pageflow\CMS_ROOT;

abstract class Element extends Presentable {

    private ?string $title = null;
    private int $elementHolderId;
    private int $orderNr;
    private bool $includeInTableOfContents = false;
    private ElementMetadataDao $metadataDao;

    public function __construct(int $scopeId, ElementMetadataDao $metadataProvider) {
        parent::__construct($scopeId);
        $this->metadataDao = $metadataProvider;
    }

    public static function constructFromRecord(array $record): Element {
        require_once CMS_ROOT . '/elements/' . $record['identifier'] . '/' . $record['domain_object'];

        // first get the element type
        $elementType = $record['classname'];

        // the constructor for each type will initialize specific metadata
        $className = "Pageflow\\Core\\elements\\" . $record['identifier'] . "\\" . $elementType;
        $element = new $className($record["scope_id"]);

        $element->setId($record['id']);
        $element->setOrderNr($record['follow_up']);
        $element->setTemplateId($record['template_id']);
        $element->setIncludeInTableOfContents($record['include_in_table_of_contents'] == 1);
        $element->setElementHolderId($record['element_holder_id']);

        $element->initializeMetaData();
        return $element;
    }

    public function setIncludeInTableOfContents(bool $includeInTableOfContents): void {
        $this->includeInTableOfContents = $includeInTableOfContents;
    }

    public function initializeMetaData(): void {
        $this->metadataDao->loadMetaData();
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getOrderNr(): int {
        return $this->orderNr;
    }

    public function setOrderNr(int $order_nr): void {
        $this->orderNr = $order_nr;
    }

    public function includeInTableOfContents(): bool {
        return $this->includeInTableOfContents;
    }

    public function getElementHolderId(): int {
        return $this->elementHolderId;
    }

    public function setElementHolderId(int $elementHolderId): void {
        $this->elementHolderId = $elementHolderId;
    }

    public function updateMetaData(): void {
        $this->metadataDao->upsert($this);
    }

    public abstract function getStatics(): Visual;

    public abstract function getBackendVisual(): ElementVisual;

    public abstract function getFrontendVisual(Page $page, ?Article $article, ?Block $block = null): FrontendVisual;

    public abstract function getRequestHandler(): HttpRequestHandler;

    public abstract function getSummaryText(): string;

    protected function getMetaDataDao(): ElementMetadataDao {
        return $this->metadataDao;
    }
}
