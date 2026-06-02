<?php

namespace Pageflow\Core\elements\list_element\visuals;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\elements\list_element\ListElement;
use Pageflow\Core\database\dao\FunctionalImageDaoMysql;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Button;
use Pageflow\Core\view\views\ElementVisual;
use Pageflow\Core\view\views\FunctionalImageLookup;
use Pageflow\Core\view\views\SingleCheckbox;
use Pageflow\Core\view\views\RichTextArea;
use Pageflow\Core\view\views\TextField;
use const Pageflow\Core\ELEMENT_HOLDER_FORM_ID;

class ListElementEditor extends ElementVisual {

    private static string $TEMPLATE = "list_element/templates/list_element_form.tpl";
    private ListElement $listElement;

    public function __construct(ListElement $listElement) {
        parent::__construct();
        $this->listElement = $listElement;
    }

    public function getElement(): Element {
        return $this->listElement;
    }

    public function getElementFormTemplateFilename(): string {
        return self::$TEMPLATE;
    }

    public function loadElementForm(TemplateData $data): void {
        $titleField = new TextField('element_' . $this->listElement->getId() . '_title', $this->getTextResource("list_element_editor_title"), $this->listElement->getTitle(), false, true, null);
        $addItemButton = new Button("", $this->getTextResource("list_element_editor_add_item"), "addListItem(" . $this->listElement->getId() . ",'" . ELEMENT_HOLDER_FORM_ID . "');");

        $functionalImageDao = FunctionalImageDaoMysql::getInstance();
        $tree = $functionalImageDao->getFolderTree();
        $allFunctionalImages = array_map(fn($img) => ['id' => $img->getId(), 'title' => $img->getTitle()], $functionalImageDao->getAllFunctionalImages());

        $data->assign("list_items", $this->getListItems());
        $data->assign("list_item_order", $this->getListItemOrder());
        $data->assign("add_item_button", $addItemButton->render());
        $data->assign("title_field", $titleField->render());
        $data->assign("id", $this->listElement->getId());
        $data->assign("all_functional_images", $allFunctionalImages);
        $data->assign("fimg_picker_root_folders", $this->buildFolderData($tree['folders']));
        $data->assign("fimg_picker_root_images", $this->buildImageData($tree['images']));
    }

    private function buildFolderData(array $folders): array {
        return array_map(function ($folder) {
            return [
                'id'          => $folder->getId(),
                'name'        => $folder->getName(),
                'sub_folders' => $this->buildFolderData($folder->getSubFolders()),
                'images'      => $this->buildImageData($folder->getImages()),
            ];
        }, $folders);
    }

    private function buildImageData(array $images): array {
        return array_map(fn($img) => ['id' => $img->getId(), 'title' => $img->getTitle()], $images);
    }

    private function getListItems(): array {
        $listItems = array();
        foreach ($this->listElement->getListItems() as $listItem) {
            $listItemValues = array();
            $itemTextField = new RichTextArea("listitem_" . $listItem->getId() . "_text", "", $listItem->getText(), false, true, "list-item-textarea list-item-rich-text");
            $deleteField = new SingleCheckbox("listitem_" . $listItem->getId() . "_delete", "", false, false, "");
            $functionalImageId = $listItem->getFunctionalImageId() ? (string)$listItem->getFunctionalImageId() : null;
            $functionalImageTitle = null; // resolved via JS picker; title not stored separately
            $functionalImageLookup = new FunctionalImageLookup(
                "listitem_" . $listItem->getId() . "_functional_image_id",
                null,
                $functionalImageId,
                $functionalImageTitle,
                $this->listElement->getId()
            );

            $listItemValues['item_text_field'] = $itemTextField->render();
            $listItemValues['delete_field'] = $deleteField->render();
            $listItemValues['functional_image_lookup'] = $functionalImageLookup->render();
            $listItemValues['functional_image_id'] = $listItem->getFunctionalImageId();
            $listItemValues['id'] = $listItem->getId();

            $listItems[] = $listItemValues;
        }
        return $listItems;
    }

    private function getListItemOrder(): string {
        $items = array_filter($this->listElement->getListItems(), fn($item) => $item->getId());
        return implode(',', array_map(fn($item) => $item->getId(), $items));
    }

}