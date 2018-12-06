<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model\ResourceModel;

class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageWorx\OptionDependency\Model\Config::TABLE_NAME, 'dependency_id');
    }

    public function allProductOptions($productId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_option'),
            ['option_id', 'mageworx_option_id']
        )->where(
            'product_id = ' . $productId
        );

        return $connection->fetchPairs($select);
    }

    /**
     * Load array of dependencies by $columns.
     * Dependencies can be:
     * 1. 'child_option_id' => 'parent_option_type_id'
     * 2. 'child_option_type_id' => 'parent_option_type_id'
     * Then the result array processed in the Model\Config.
     *
     * @param int $productId
     * @param array $columns
     * @return array
     */
    public function loadDependencies($productId, $columns = [])
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable(\MageWorx\OptionDependency\Model\Config::TABLE_NAME),
            $columns
        )->where(
            'product_id = ' . $productId
        );

        return $connection->fetchAll($select);
    }

    /**
     * Load array of option types.
     * ['mageworx_option_id' => 'type']
     *
     * @param int $productId
     * @return array
     */
    public function loadOptionTypes($productId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_option'),
            ['mageworx_option_id', 'type']
        )->where(
            'product_id = ' . $productId
        );

        return $connection->fetchPairs($select);
    }

    /**
     * Load mageworx_option_id array by option_id array
     *
     * @param array $ids
     * @return array
     */
    public function loadMageworxOptionId($ids = [])
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_option'),
            ['option_id', 'mageworx_option_id']
        )->where(
            'option_id IN (?)',
            $ids
        );

        return $connection->fetchPairs($select);
    }

    /**
     * Load mageworx_option_type_id array by option_type_id array
     *
     * @param array $ids
     * @return array
     */
    public function loadMageworxOptionTypeId($ids = [])
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_option_type_value'),
            ['option_type_id', 'mageworx_option_type_id']
        )->where(
            'option_type_id IN (?)',
            $ids
        );

        return $connection->fetchPairs($select);
    }

    /**
     *
     * @param array $parentValueId
     * @return array
     */
    public function getIsDisabledData($parentValueId = [])
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(
                           ['main_table' => $this->getTable('catalog_product_option_type_value')],
                           ['disabled']
                       )
                       ->join(
                           ['dependency_table' => $this->getTable('mageworx_option_dependency')],
                           'main_table.mageworx_option_type_id = dependency_table.parent_option_type_id',
                           ['parent_option_type_id']
                       )
                       ->where('dependency_table.parent_option_type_id IN (?)', $parentValueId);

        return $this->getConnection()->fetchAll($select);
    }
}
