<?php

namespace Pageflow\Core\core\model;

use DateTime;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\modules\templates\model\Presentable;
use Pageflow\Core\core\model\ElementHolderType;

class ElementHolder extends Presentable {

    private string $name;
    private string $title;
    private bool $published;
    private string $createdAt;
    private int $createdById;
    private DateTime $lastModified;
    private array $elements = array();
    private ElementHolderType $type;
    private int $version = 1;

    public function __construct(int $scopeId, ElementHolderType $type) {
        parent::__construct($scopeId);
        $this->type = $type;
    }

    public static function constructFromRecord(array $row): ElementHolder {
        $elementHolder = new ElementHolder($row["scope_id"], ElementHolderType::from($row['type']));
        $elementHolder->initFromDb($row);

        return $elementHolder;
    }

    public function setCreatedById(int $createdById): void {
        $this->createdById = $createdById;
    }

    public function isPublished(): bool {
        return $this->published;
    }

    public function setPublished(bool $published): void {
        $this->published = $published;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function addElement(Element $element): void {
        $this->elements[] = $element;
    }

    public function deleteElement(Element $element_to_delete): void {
        $this->elements = array_filter($this->elements, fn($element) => $element->getId() != $element_to_delete->getId());
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function setCreatedAt(string $created_at): void {
        $this->createdAt = $created_at;
    }

    public function getCreatedById(): int {
        return $this->createdById;
    }

    public function getLastModified(): DateTime {
        return $this->lastModified;
    }

    public function setLastModified(DateTime $last_modified): void {
        $this->lastModified = $last_modified;
    }

    public function getElementStatics(): array {
        $elementDao = ElementDaoMysql::getInstance();
        $element_statics = array();
        foreach ($this->getElements() as $element) {
            $key = $elementDao->getElementTypeForElement($element->getId())->getIdentifier();
            if (!array_key_exists($key, $element_statics)) {
                $statics = $element->getStatics();
                if (!is_null($statics)) {
                    $element_statics[$key] = $element->getStatics();
                }
            }
        }
        return $element_statics;
    }

    public function getElements(): array {
        usort($this->elements, function (Element $e1, Element $e2) {
            return $e1->getOrderNr() - $e2->getOrderNr();
        });
        return $this->elements;
    }

    public function setElements(array $elements): void {
        $this->elements = $elements;
    }

    public function getType(): ElementHolderType {
        return $this->type;
    }

    public function setType(ElementHolderType $type): void {
        $this->type = $type;
    }

    public function getVersion(): int {
        return $this->version;
    }

    public function setVersion(int $version): void {
        $this->version = $version;
    }

    protected function initFromDb(array $row): void {
        $this->setName($row['name']);
        $this->setTitle($row['title']);
        $this->setPublished($row['published'] == 1);
        $this->setCreatedAt($row['created_at']);
        $this->setCreatedById($row['created_by']);
        $this->setLastModified(new DateTime($row['last_modified']));
        $this->setType(ElementHolderType::from($row['type']));
        $this->setVersion((int)($row['version'] ?? 1));
        parent::initFromDb($row);
        $this->setElements(ElementDaoMysql::getInstance()->getElements($this));
    }

}