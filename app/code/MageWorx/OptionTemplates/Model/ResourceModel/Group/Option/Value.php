<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel\Group\Option;

/**
 * Group custom option resource model
 *
 */
class Value extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_optiontemplates_group_option_type_value', 'option_type_id');
    }

    /**
     * Get real table name for db table, validated by db adapter
     * Replace product option tables to mageworx group option tables
     *
     * @param string $origTableName
     * @return string
     *
     */
    public function getTable($origTableName)
    {
        $origTableName = parent::getTable($origTableName);

        switch ($origTableName) {
            case parent::getTable('catalog_product_option'):
                $tableName = 'mageworx_optiontemplates_group_option';
                break;
            case parent::getTable('catalog_product_option_title'):
                $tableName = 'mageworx_optiontemplates_group_option_title';
                break;
            case parent::getTable('catalog_product_option_price'):
                $tableName = 'mageworx_optiontemplates_group_option_price';
                break;
            case parent::getTable('catalog_product_option_type_value'):
                $tableName = 'mageworx_optiontemplates_group_option_type_value';
                break;
            case parent::getTable('catalog_product_option_type_title'):
                $tableName = 'mageworx_optiontemplates_group_option_type_title';
                break;
            case parent::getTable('catalog_product_option_type_price'):
                $tableName = 'mageworx_optiontemplates_group_option_type_price';
                break;
            default:
                $tableName = $origTableName;
        }
        return parent::getTable($tableName);
    }

    /**
     * Delete values by option type
     *
     * @param int $optionTypeId
     * @return void
     */
    public function deleteValues($optionTypeId)
    {
        $condition = ['option_type_id = ?' => $optionTypeId];

        $this->getConnection()->delete($this->getTable('catalog_product_option_type_value'), $condition);
    }
}
