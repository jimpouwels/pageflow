<?php

namespace Pageflow\Core\database\dao;


use Pageflow\Core\modules\webforms\handlers\FormHandler;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformHandlerProperty;
use Pageflow\Core\modules\webforms\model\WebformItem;

interface WebformDao {
    public function getWebForm(int $webformId): ?WebForm;

    public function getAllWebForms(): array;

    public function persistWebForm(WebForm $webform): void;

    public function updateWebForm(WebForm $webform): void;

    public function deleteWebForm(WebForm $webform): void;

    public function persistWebFormItem(WebForm $webform, WebFormItem $webformItem): void;

    public function updateWebFormItem(WebFormItem $webformItem): void;

    public function deleteWebFormItem(int $itemId): void;

    public function getWebFormItem(int $id): ?WebFormItem;

    public function getWebFormItemsByWebForm(int $webformId): array;

    public function addWebFormHandler(WebForm $webform, FormHandler $handler);

    public function getWebFormHandlersFor(WebForm $webform): array;

    public function deleteWebFormHandler(WebForm $webform, int $webformHandlerId): void;

    public function updateWebFormHandlerOrder(int $webformHandlerId, int $orderNr): void;

    public function storeProperty(int $handler_id, WebformHandlerProperty $property): void;

    public function deleteProperty(WebformHandlerProperty $webFormHandlerProperty): void;

    public function updateHandlerProperty(WebformHandlerProperty $property): void;
}