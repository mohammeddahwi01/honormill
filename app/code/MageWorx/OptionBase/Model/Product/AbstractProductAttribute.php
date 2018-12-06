<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Product;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\ProductAttributeInterface;

abstract class AbstractProductAttribute implements ProductAttributeInterface
{
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
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function clearData()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyData($entity)
    {
        $this->entity = $entity;

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemByProduct($product)
    {
        return;
    }
}
