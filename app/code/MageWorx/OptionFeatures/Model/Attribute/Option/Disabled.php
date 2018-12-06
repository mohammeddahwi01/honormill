<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Disabled extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_DISABLED;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataBeforeSave($option)
    {
        $isDisabledOption = false;
        if (is_object($option) && $option->getValues()) {
            $isDisabledOption = $this->checkDisabled($option->getValues());
        } elseif (!empty($option['values']) && is_array($option['values'])) {
            $isDisabledOption = $this->checkDisabled($option['values']);
        }
        return (int)$isDisabledOption;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * Check values for Disabled flag
     * if ALL are 'disabled' - return true
     * if ANY is NOT 'disabled' - return false
     *
     * @param array $values
     * @return bool $isDisabledOption
     */
    protected function checkDisabled($values)
    {
        foreach ($values as $value) {
            if (empty($value['disabled'])) {
                return false;
            }
        }
        return true;
    }
}
