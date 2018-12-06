<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionLink\Model\ResourceModel\Product\Option\Value;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class FieldFactory. Load field object.
 */
class FieldFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * FieldFactory constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create option field object.
     *
     * @param $productAttribute
     * @param array $arguments
     * @return mixed
     */
    public function create($field, array $arguments = [])
    {
        $fieldPath = '\MageWorx\OptionLink\Model\ResourceModel\Product\Option\Value\Fields\\';

        $fieldName = ucfirst($field);

        $instance = $this->objectManager->create(
            $fieldPath . $fieldName,
            $arguments
        );

        return $instance;
    }
}
