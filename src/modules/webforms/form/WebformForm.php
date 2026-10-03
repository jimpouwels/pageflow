<?php

namespace Pageflow\Core\modules\webforms\form;

use Pageflow\Core\core\form\Form;
use Pageflow\Core\core\form\FormException;
use Pageflow\Core\database\dao\WebformDao;
use Pageflow\Core\database\dao\WebformDaoMysql;
use Pageflow\Core\modules\webforms\model\Webform;
use Pageflow\Core\modules\webforms\WebformHandlerManager;
use Pageflow\Core\modules\webforms\WebformItemFactory;
use Pageflow\Core\utilities\Arrays;

class WebformForm extends Form {

    private WebformItemFactory $webformItemFactory;
    private WebformHandlerManager $webformHandlerManager;
    private WebForm $webform;
    private array $handlerProperties = array();
    private ?string $captchaSecret = null;
    private WebformDao $webformDao;

    public function __construct(WebForm $webform) {
        $this->webform = $webform;
        $this->webformItemFactory = WebformItemFactory::getInstance();
        $this->webformDao = WebformDaoMysql::getInstance();
        $this->webformHandlerManager = WebformHandlerManager::getInstance();
    }

    public function loadFields(): void {
        $this->webform->setTitle($this->getMandatoryFieldValue("title"));
        $this->webform->setTemplateId($this->getFieldValue("template"));
        $this->webform->setIncludeCaptcha($this->getCheckboxValue('include_captcha'));

        // delete properties that are no longer supposed to be there
        $allHandlerInstances = $this->webformDao->getWebFormHandlersFor($this->webform);
        foreach ($allHandlerInstances as $handlerInstance) {
            foreach ($handlerInstance->getProperties() as $property) {
                if (!Arrays::firstMatch($this->webformHandlerManager->getHandler($handlerInstance->getType())->getRequiredProperties(), function ($requiredProperty) use ($property) {
                    return $property->getName() == $requiredProperty->getName();
                })) {
                    $this->webformDao->deleteProperty($property);
                }
            }
        }
        $this->loadHandlerProperties();

        if ($this->webform->getIncludeCaptcha()) {
            $this->webform->setCaptchaKey($this->getMandatoryFieldValue('captcha_key'));
            $this->captchaSecret = $this->getMandatoryFieldValue('captcha_secret');
        }

        $itemOrder = $this->getFieldValue('draggable_order');
        $itemOrderArray = array();
        if ($itemOrder) {
            $itemOrderArray = explode(',', $itemOrder);
        }
        foreach ($this->webform->getFormFields() as $formField) {
            if (count($itemOrderArray) > 0) {
                $formField->setOrderNr(array_search($formField->getId(), $itemOrderArray));
            }
            $form = $this->webformItemFactory->getBackendFormFor($formField);
            if ($form->supports($formField->getType())) {
                $form->loadFields();
            }
        }

        foreach ($this->handlerProperties as $property) {
            $property->setValue($this->getMandatoryFieldValue("handler_property_{$property->getId()}_field"));
        }

        $handlerOrder = $this->getFieldValue('webform_handlers_order');
        if ($handlerOrder) {
            $handlerOrderArray = explode(',', $handlerOrder);
            foreach ($this->webformDao->getWebFormHandlersFor($this->webform) as $handlerInstance) {
                $orderNr = array_search($handlerInstance->getId(), $handlerOrderArray);
                if ($orderNr !== false) {
                    $this->webformDao->updateWebFormHandlerOrder($handlerInstance->getId(), $orderNr);
                }
            }
        }

        if ($this->hasErrors()) {
            throw new FormException();
        }
    }

    public function getHandlerProperties(): array {
        return $this->handlerProperties;
    }

    public function getCaptchaSecret(): ?string {
        return $this->captchaSecret;
    }

    private function loadHandlerProperties(): void {
        foreach ($this->webformDao->getWebFormHandlersFor($this->webform) as $handlerInstance) {
            $this->handlerProperties = array_merge($this->handlerProperties, $handlerInstance->getProperties());
        }
    }
}