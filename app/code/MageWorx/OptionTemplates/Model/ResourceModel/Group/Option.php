<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel\Group;

class Option extends \Magento\Catalog\Model\ResourceModel\Product\Option
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_optiontemplates_group_option', 'option_id');
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
     * Delete option
     *
     * @param int $optionId
     * @return void
     */
    public function deleteOldOptions($groupId)
    {
        $condition = ['group_id = ?' => $groupId];

        $this->getConnection()->delete($this->getTable('catalog_product_option'), $condition);
    }
}
