<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

interface ProductAttributeInterface
{
    /**
     * Get array of attribute keys
     */
    public function getKeys();

    /**
     * Get table name, used when attribute use individual tables
     */
    public function getTableName();

    /**
     * Apply attribute data
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     */
    public function applyData($entity);

    /**
     * Clear attribute data
     */
    public function clearData();

    /**
     * Get object item by product ID
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getItemByProduct($product);
}
