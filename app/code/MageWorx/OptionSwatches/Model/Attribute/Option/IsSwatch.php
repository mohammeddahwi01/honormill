<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionSwatches\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionSwatches\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class IsSwatch extends AbstractAttribute implements AttributeInterface
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
        return Helper::KEY_IS_SWATCH;
    }


    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }
}
