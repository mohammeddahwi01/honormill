<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

interface AttributeInterface
{
    /**
     * Get attribute name
     */
    public function getName();

    /**
     * Check if attribute has own table in database
     */
    public function hasOwnTable();

    /**
     * Get table name, used when attribute use individual tables
     *
     * @param string $type
     */
    public function getTableName($type = '');

    /**
     * Collect attribute data
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     * @param array $options
     */
    public function collectData($entity, $options);

    /**
     * Delete old attribute data
     *
     * @param array $data
     */
    public function deleteOldData($data);

    /**
     * Prepare attribute data for frontend js config
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     */
    public function prepareDataForFrontend($object);

    /**
     * Prepare attribute data before save
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     */
    public function prepareDataBeforeSave($object);
}
