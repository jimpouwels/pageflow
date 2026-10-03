<?php

namespace Pageflow\Core\database\dao;

use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\webforms\handlers\FormHandler;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\model\WebformButton;
use Pageflow\Core\modules\webforms\model\WebformDropdown;
use Pageflow\Core\modules\webforms\model\WebformField;
use Pageflow\Core\modules\webforms\model\WebformHandlerInstance;
use Pageflow\Core\modules\webforms\model\WebformHandlerProperty;
use Pageflow\Core\modules\webforms\model\WebformItem;
use Pageflow\Core\modules\webforms\model\WebformTextArea;
use Pageflow\Core\modules\webforms\model\WebformTextfield;

class WebformDaoMysql implements WebformDao {

    private static string $myAllColumns = "i.id, i.title, i.template_id, i.include_captcha, i.captcha_key, s.id as scope_id";
    private static ?WebformDaoMysql $instance = null;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): WebformDaoMysql {
        if (!self::$instance) {
            self::$instance = new WebformDaoMysql();
        }
        return self::$instance;
    }

    public function getWebForm(int $webformId): ?WebForm {
        $query = "SELECT " . self::$myAllColumns . " FROM webforms i, scopes s WHERE i.id = {$webformId} AND s.id = " . WebForm::$SCOPE;
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            return WebForm::constructFromRecord($row, $this->getWebFormItemsByWebForm($webformId));
        }
        return null;
    }

    public function getAllWebForms(): array {
        $webforms = array();
        $query = "SELECT " . self::$myAllColumns . " FROM webforms i, scopes s WHERE s.id = " . WebForm::$SCOPE;
        $result = $this->mysqlConnector->executeQuery($query);
        while ($row = $result->fetch_assoc()) {
            $webforms[] = WebForm::constructFromRecord($row, $this->getWebFormItemsByWebForm($row["id"]));
        }
        return $webforms;
    }

    public function persistWebForm(WebForm $webform): void {
        $query = "INSERT INTO webforms (title, include_captcha) VALUES (?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $title = $webform->getTitle();
        $includeCaptcha = 0;
        $statement->bind_param('si', $title, $includeCaptcha);
        $this->mysqlConnector->executeStatement($statement);
        $webform->setId($this->mysqlConnector->getInsertId());
    }

    public function updateWebForm(WebForm $webform): void {
        $query = "UPDATE webforms SET title = ?, template_id = ?, include_captcha = ?, captcha_key = ? WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $webform->getId();
        $title = $webform->getTitle();
        $templateId = $webform->getTemplateId();
        $includeCaptcha = $webform->getIncludeCaptcha() ? 1 : 0;
        $captchaKey = $webform->getCaptchaKey();
        $statement->bind_param("siisi", $title, $templateId, $includeCaptcha, $captchaKey, $id);
        $this->mysqlConnector->executeStatement($statement);
        foreach ($webform->getFormFields() as $form_field) {
            $this->updateWebFormItem($form_field);
        }
    }

    public function deleteWebForm(WebForm $webform): void {
        $query = 'DELETE FROM webforms WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $webform->getId();
        $statement->bind_param('i', $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function persistWebFormItem(WebForm $webform, WebFormItem $webformItem): void {
        $query = "INSERT INTO webforms_fields (label, `name`, mandatory, webform_id, `type`, scope_id) VALUE (?, ?, ?, ?, ?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $label = $webformItem->getLabel();
        $name = $webformItem->getName();
        $webformId = $webform->getId();

        $mandatory = false;
        if ($webformItem instanceof WebFormField) {
            $mandatory = $webformItem->getMandatory() ? 1 : 0;
        }

        $type = $webformItem->getType();
        $scopeId = $webformItem->getScopeId();
        $statement->bind_param("ssiisi", $label, $name, $mandatory, $webformId, $type, $scopeId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateWebFormItem(WebFormItem $webformItem): void {
        $query = "UPDATE webforms_fields SET `name` = ?, label = ?, template_id = ?, mandatory = ?, order_nr = ? WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $label = $webformItem->getLabel();
        $name = $webformItem->getName();
        $orderNr = $webformItem->getOrderNr();
        $templateId = $webformItem->getTemplateId();
        $webformFieldId = $webformItem->getId();

        $mandatory = 0;
        if ($webformItem instanceof WebFormField) {
            $mandatory = $webformItem->getMandatory() ? 1 : 0;
        }
        $statement->bind_param("ssiiii", $name, $label, $templateId, $mandatory, $orderNr, $webformFieldId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function deleteWebFormItem(int $itemId): void {
        $query = 'DELETE FROM webforms_fields WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $itemId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getWebFormItem(int $id): ?WebFormItem {
        $query = 'SELECT * FROM webforms_fields WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            switch ($row["type"]) {
                case WebformTextfield::$TYPE:
                    return WebformTextfield::constructFromRecord($row);
                case WebFormTextArea::$TYPE:
                    return WebFormTextArea::constructFromRecord($row);
                case WebformDropdown::$TYPE:
                    return WebformDropdown::constructFromRecord($row);
                case WebformButton::$TYPE:
                    return WebformButton::constructFromRecord($row);
            }
        }
        return null;
    }

    public function getWebFormItemsByWebForm(int $webformId): array {
        $formFields = array();
        $query = "SELECT * FROM webforms_fields WHERE webform_id = ? ORDER BY order_nr ASC";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param("i", $webformId);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            switch ($row["type"]) {
                case WebformTextfield::$TYPE:
                    $formFields[] = WebformTextfield::constructFromRecord($row);
                    break;
                case WebFormTextArea::$TYPE:
                    $formFields[] = WebFormTextArea::constructFromRecord($row);
                    break;
                case WebformDropdown::$TYPE:
                    $formFields[] = WebformDropdown::constructFromRecord($row);
                    break;
                case WebformButton::$TYPE:
                    $formFields[] = WebformButton::constructFromRecord($row);
                    break;
            }
        }
        return $formFields;
    }

    public function addWebFormHandler(WebForm $webform, FormHandler $handler) {
        $query = 'INSERT INTO webforms_handlers (`type`, webform_id) VALUES (?, ?)';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $type = $handler->getType();
        $webformId = $webform->getId();
        $statement->bind_param('si', $type, $webformId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function getWebFormHandlersFor(WebForm $webform): array {
        $query = 'SELECT * FROM webforms_handlers WHERE webform_id = ? ORDER BY order_nr ASC';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $webformId = $webform->getId();
        $statement->bind_param('i', $webformId);

        $result = $this->mysqlConnector->executeStatement($statement);

        $handlers = array();
        while ($row = $result->fetch_assoc()) {
            $handlers[] = WebFormHandlerInstance::constructFromRecord($row, $this->getPropertiesFor($row['id']));
        }
        return $handlers;
    }

    public function deleteWebFormHandler(WebForm $webform, int $webformHandlerId): void {
        $query = 'DELETE FROM webforms_handlers WHERE webform_id = ? AND `id` = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $webformId = $webform->getId();
        $statement->bind_param('ii', $webformId, $webformHandlerId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateWebFormHandlerOrder(int $webformHandlerId, int $orderNr): void {
        $query = 'UPDATE webforms_handlers SET order_nr = ? WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('ii', $orderNr, $webformHandlerId);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function storeProperty(int $handler_id, WebformHandlerProperty $property): void {
        $name = $property->getName();
        $type = $property->getType();
        $query = "INSERT INTO webforms_handlers_properties (handler_id, `name`, `value`, `type`) VALUES ('{$handler_id}', '{$name}', '', '{$type}')";
        $this->mysqlConnector->executeQuery($query);
        $property->setId($this->mysqlConnector->getInsertId());
    }

    public function deleteProperty(WebformHandlerProperty $webFormHandlerProperty): void {
        $query = 'DELETE FROM webforms_handlers_properties WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $id = $webFormHandlerProperty->getId();
        $statement->bind_param('i', $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateHandlerProperty(WebformHandlerProperty $property): void {
        $query = 'UPDATE webforms_handlers_properties SET `value` = ? WHERE id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);

        $id = $property->getId();
        $value = $property->getValue();
        $statement->bind_param('si', $value, $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    private function getPropertiesFor(int $handler_id): array {
        $query = 'SELECT * FROM webforms_handlers_properties WHERE handler_id = ?';
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param('i', $handler_id);
        $result = $this->mysqlConnector->executeStatement($statement);

        $properties = array();
        while ($row = $result->fetch_assoc()) {
            $properties[] = WebformHandlerProperty::constructFromRecord($row);
        }
        return $properties;
    }
}