<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefault;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeIsDefault\Collection as IsDefaultCollection;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefaultFactory as IsDefaultFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class IsCustomTab extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_IS_CUSTOMTAB;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOldData($data)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, $options)
    {
        $this->entity = $entity;
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($object)
    {
        return '';
    }
}
