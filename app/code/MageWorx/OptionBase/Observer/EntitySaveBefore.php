<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\ManagerInterface;
use MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use Magento\Catalog\Model\Product\Option;

class EntitySaveBefore implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var Option
     */
    protected $optionEntity;

    /**
     * @param ManagerInterface $eventManager
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     */
    public function __construct(
        ManagerInterface $eventManager,
        OptionValueAttributes $optionValueAttributes,
        OptionAttributes $optionAttributes,
        Option $optionEntity
    ) {
        $this->optionValueAttributes = $optionValueAttributes;
        $this->optionAttributes = $optionAttributes;
        $this->eventManager = $eventManager;
        $this->optionEntity = $optionEntity;
    }

    /**
     *
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $processedOptions = [];
        $entity = $observer->getGroup() ?: $observer->getProduct();
        if (!$entity || !$entity->getData('options')) {
            return $this;
        }

        foreach ($entity->getData('options') as $optionIndex => $option) {
            foreach ($this->optionAttributes->getData() as $attribute) {
                $option[$attribute->getName()] = $attribute->prepareDataBeforeSave($option);
            }

            if (isset($option['type'])
                && $this->optionEntity->getGroupByType($option['type']) === Option::OPTION_GROUP_SELECT
            ) {
                $processedValues = [];
                $values = [];
                if (is_object($option) && $option->getData('values')) {
                    $values = $option->getData('values');
                } elseif (!empty($option['values']) && is_array($option['values'])) {
                    $values = $option['values'];
                }
                foreach ($values as $valueIndex => $value) {
                    foreach ($this->optionValueAttributes->getData() as $valueAttribute) {
                        $value[$valueAttribute->getName()] = $valueAttribute->prepareDataBeforeSave($value);
                    }
                    $processedValues[$valueIndex] = $value;
                }
                $option['values'] = $processedValues;
            }
            $processedOptions[$optionIndex] = $option;
        }

        if ($observer->getGroup()) {
            $entity->setData('options', $processedOptions);
            $entity->setData('product_options', $processedOptions);
        } else {
            $entity->setOptions($processedOptions);
        }
        return $this;
    }
}
