<?php

namespace Pageflow\Core\request_handlers;

use Pageflow\Core\core\model\Element;
use Pageflow\Core\core\form\ElementHolderForm;
use Pageflow\Core\core\form\FormException;
use Pageflow\Core\core\model\ElementHolder;
use Pageflow\Core\core\model\ElementType;
use Pageflow\Core\database\dao\ElementDao;
use Pageflow\Core\database\dao\ElementDaoMysql;
use Pageflow\Core\elements\ElementContainsErrorsException;
use Pageflow\Core\elements\element_container_element\ElementContainerElement;
use Pageflow\Core\service\ElementHolderService;
use Pageflow\Core\service\ElementHolderInteractor;
use Pageflow\Core\request_handlers\exceptions\ElementHolderContainsErrorsException;
use Pageflow\Core\request_handlers\exceptions\VersionConflictException;
use const Pageflow\Core\ADD_ELEMENT_FORM_ID;
use const Pageflow\Core\DELETE_ELEMENT_FORM_ID;
use const Pageflow\Core\TARGET_CONTAINER_FORM_ID;
use const Pageflow\Core\DELETE_CONTAINER_CHILDREN_FORM_ID;
use const Pageflow\Core\ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID;

abstract class ElementHolderRequestHandler extends HttpRequestHandler {

    private ElementDao $elementDao;
    private ElementHolderService $elementHolderService; 

    public function __construct() {
        $this->elementDao = ElementDaoMysql::getInstance();
        $this->elementHolderService = new ElementHolderInteractor();
    }

    public function handleGet(): void {}

    public function handlePost(): void {
        if (!$this->getElementHolderFromPostRequest()) {
            return;
        }
        $holder = $this->getElementHolderFromPostRequest();
        $submittedVersion = isset($_POST['element_holder_version']) ? (int)$_POST['element_holder_version'] : null;
        if ($submittedVersion !== null && $submittedVersion !== $holder->getVersion()) {
            throw new VersionConflictException();
        }
        if ($this->isAddElementAction()) {
            $this->addElement($this->getElementHolderFromPostRequest());
            $this->updateElementHolder($this->getElementHolderFromPostRequest());
        } else if ($this->isDeleteElementAction()) {
            (new ElementHolderForm($this->getElementHolderFromPostRequest()))->loadContainerAssignments();
            $this->deleteElementFrom($this->getElementHolderFromPostRequest());
            $this->updateElementHolder($this->getElementHolderFromPostRequest());
        } else {
            $this->updateElementHolder($this->getElementHolderFromPostRequest());
        }
    }

    protected abstract function getElementHolderFromPostRequest(): ?ElementHolder;

    protected function updateElementHolder(ElementHolder $elementHolder): void {
        $errorThrown = false;
        $form = new ElementHolderForm($elementHolder);
        $form->loadFields();
        foreach ($elementHolder->getElements() as $element) {
            try {
                $element->getRequestHandler()->handle();
            } catch (ElementContainsErrorsException|FormException $e) {
                $errorThrown = true;
            }
        }
        if ($errorThrown) {
            throw new ElementHolderContainsErrorsException();
        }
    }

    private function addElement(ElementHolder $elementHolder): void {
        $elementType = $this->getElementTypeToAdd();
        $createdElement = $this->elementHolderService->addElementToElementHolder($elementType, $elementHolder);

        $targetContainerId = $this->getTargetContainerId();
        if ($targetContainerId !== null) {
            $createdElement->setContainerId($targetContainerId);
            $this->elementDao->updateElement($createdElement);
        }

        // Handle insert position if specified
        if (isset($_POST['element_insert_position'])) {
            $insertPosition = intval($_POST['element_insert_position']);
            $this->reorderElementsAfterInsert($elementHolder, $createdElement, $insertPosition);
        }
    }

    private function getTargetContainerId(): ?int {
        if (isset($_POST[TARGET_CONTAINER_FORM_ID]) && $_POST[TARGET_CONTAINER_FORM_ID] != "") {
            return (int)$_POST[TARGET_CONTAINER_FORM_ID];
        }
        return null;
    }

    private function reorderElementsAfterInsert(ElementHolder $elementHolder, Element $newElement, int $insertPosition): void {
        $newElementId = $newElement->getId();
        // Only reorder among elements in the same scope (top level, or the same container) as the newly inserted element
        $elements = array_values(array_filter($elementHolder->getElements(), fn($element) => $element->getContainerId() == $newElement->getContainerId() && $element->getId() != $newElementId));

        $existingOrderIds = array();
        if (isset($_POST['draggable_order']) && !empty($_POST['draggable_order'])) {
            foreach (explode(',', $_POST['draggable_order']) as $id) {
                $id = trim($id);
                if ($id !== '') $existingOrderIds[] = $id;
            }
        } else {
            // No posted order yet (e.g. very first element added): fall back to the saved order.
            foreach ($elementHolder->getElements() as $element) {
                if ($element->getId() != $newElementId) $existingOrderIds[] = $element->getId();
            }
        }

        // Find the sibling (within the same scope) the new element should be inserted after.
        $elementIdsInScope = array_map(fn($element) => $element->getId(), $elements);
        $siblingIdsInPostedOrder = array_values(array_filter($existingOrderIds, fn($id) => in_array((int)$id, $elementIdsInScope)));
        $insertAfterId = $insertPosition > 0 && isset($siblingIdsInPostedOrder[$insertPosition - 1])
            ? $siblingIdsInPostedOrder[$insertPosition - 1]
            : null;

        $splicedOrderIds = array();
        $inserted = false;
        if ($insertAfterId === null) {
            $splicedOrderIds[] = $newElementId;
            $inserted = true;
        }
        foreach ($existingOrderIds as $id) {
            $splicedOrderIds[] = $id;
            if (!$inserted && (int)$id === (int)$insertAfterId) {
                $splicedOrderIds[] = $newElementId;
                $inserted = true;
            }
        }
        if (!$inserted) {
            $splicedOrderIds[] = $newElementId;
        }

        $_POST['draggable_order'] = implode(',', $splicedOrderIds);
    }

    private function deleteElementFrom(ElementHolder $elementHolder): void {
        $elementToDelete = $this->elementDao->getElement($_POST[DELETE_ELEMENT_FORM_ID]);
        if ($elementToDelete) {
            if ($elementToDelete instanceof ElementContainerElement) {
                $this->deleteContainerElement($elementHolder, $elementToDelete);
            } else {
                $this->elementDao->deleteElement($elementToDelete);
                $elementHolder->deleteElement($elementToDelete);
            }
        }
    }

    private function deleteContainerElement(ElementHolder $elementHolder, ElementContainerElement $container): void {
        $deleteChildren = isset($_POST[DELETE_CONTAINER_CHILDREN_FORM_ID]) && $_POST[DELETE_CONTAINER_CHILDREN_FORM_ID] == '1';
        // Use the actual element instances tracked by $elementHolder (not a separately
        // queried copy), and collect ALL descendants recursively (not just direct
        // children), since a direct child can itself be a nested container with its
        // own children. updateElementHolder() re-saves every element still left in
        // $elementHolder's own list right after this via each element's own request
        // handler - so any descendant that's missed here (e.g. a grandchild inside a
        // nested container) would still hold a stale containerId pointing at an
        // already-deleted container row, causing a foreign key violation.
        $descendants = $this->collectDescendantElements($elementHolder, $container->getId());
        $keptDescendantIds = array();
        $deletedDescendantIds = array();
        if ($deleteChildren) {
            foreach ($descendants as $descendant) {
                $deletedDescendantIds[] = $descendant->getId();
                $this->elementDao->deleteElement($descendant);
                $elementHolder->deleteElement($descendant);
            }
        } else {
            $container->detachChildElements();
            foreach ($descendants as $descendant) {
                if ($descendant->getContainerId() == $container->getId()) {
                    $descendant->setContainerId(null);
                    $keptDescendantIds[] = $descendant->getId();
                }
            }
        }
        $this->elementDao->deleteElement($container);
        $elementHolder->deleteElement($container);

        // The client's posted element_container_assignments JSON reflects the DOM state
        // BEFORE this delete happened, so it still maps these (now unparented or deleted)
        // descendants to the just-deleted container id. ElementHolderForm::loadContainerAssignments(),
        // called right after this via updateElementHolder(), would otherwise blindly
        // reapply that stale mapping and overwrite the fix we just made above - causing a
        // foreign key violation when each element is subsequently re-saved. Sanitize the
        // posted JSON here so that later step sees consistent, up-to-date data.
        $this->sanitizeContainerAssignments($keptDescendantIds, $deletedDescendantIds);
    }

    // Recursively collects all descendant elements (direct children, and children of
    // any nested container children, and so on) of the given container element id.
    private function collectDescendantElements(ElementHolder $elementHolder, int $containerElementId): array {
        $directChildren = array_values(array_filter($elementHolder->getElements(), fn($element) => $element->getContainerId() == $containerElementId));
        $descendants = $directChildren;
        foreach ($directChildren as $child) {
            if ($child instanceof ElementContainerElement) {
                $descendants = array_merge($descendants, $this->collectDescendantElements($elementHolder, $child->getId()));
            }
        }
        return $descendants;
    }

    // Updates the posted element_container_assignments JSON in-place so that
    // ElementHolderForm::loadContainerAssignments() (called shortly after this, from
    // updateElementHolder()) sees the post-delete state instead of stale pre-delete
    // client data: kept descendants must map to null (unparented), and deleted
    // descendants must be removed from the map entirely (they no longer exist).
    private function sanitizeContainerAssignments(array $keptDescendantIds, array $deletedDescendantIds): void {
        if (!isset($_POST[ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID]) || $_POST[ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID] === '') {
            return;
        }
        $assignments = json_decode($_POST[ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID], true);
        if (!is_array($assignments)) {
            return;
        }
        foreach ($keptDescendantIds as $id) {
            $assignments[$id] = null;
        }
        foreach ($deletedDescendantIds as $id) {
            unset($assignments[$id]);
        }
        $_POST[ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID] = json_encode($assignments);
    }

    private function getElementTypeToAdd(): ElementType {
        $elementTypeToAdd = $_POST[ADD_ELEMENT_FORM_ID];
        return $this->elementDao->getElementType($elementTypeToAdd);
    }

    private function isAddElementAction(): bool {
        return isset($_POST[ADD_ELEMENT_FORM_ID]) && $_POST[ADD_ELEMENT_FORM_ID] != "";
    }

    private function isDeleteElementAction(): bool {
        return isset($_POST[DELETE_ELEMENT_FORM_ID]) && $_POST[DELETE_ELEMENT_FORM_ID] != "";
    }
}
