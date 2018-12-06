<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Model;

/**
 * Class InstallSchema.
 * This class contains array of module fields
 * which should be added to Magento and MageWorx OptionTemplates tables.
 * @package MageWorx\OptionInventory\Model
 */
class InstallSchema implements \MageWorx\OptionBase\Model\InstallSchemaInterface
{
    /**
     * Retrieve module fileds data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => 'manage_stock',
                'params' => [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length'    => '5',
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Manage Stock (added by MageWorx OptionInventory)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => 'qty',
                'params' => [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'unsigned'  => false,
                    'nullable'  => true,
                    'comment'   => 'Option Value Qty (added by MageWorx OptionInventory)',
                ]
            ]
        ];

        return $dataArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleTablePrefix()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getForeignKeys()
    {
        return [];
    }
}
