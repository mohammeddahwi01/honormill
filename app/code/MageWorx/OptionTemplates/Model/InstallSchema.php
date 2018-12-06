<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionTemplates\Model;

use MageWorx\OptionTemplates\Helper\Data as Helper;
use Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements \MageWorx\OptionBase\Model\InstallSchemaInterface
{
    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return '';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            [
                'table_name' => 'catalog_product_option',
                'field_name' => 'group_option_id',
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment'   => 'Group Option Id (added by MageWorx OptionTemplates)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => 'group_option_value_id',
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Group Option Value Id (added by MageWorx OptionTemplates)',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP,
                'field_name' => Helper::COLUMN_NAME_GROUP_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Group ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP,
                'field_name' => Helper::COLUMN_NAME_TITLE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Title',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP,
                'field_name' => Helper::COLUMN_NAME_UPDATED_AT,
                'params' => [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment'   => 'Last Modify Date',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP,
                'field_name' => Helper::COLUMN_NAME_IS_ACTIVE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'default' => '0',
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Is Active',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => Helper::COLUMN_NAME_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => Helper::COLUMN_NAME_GROUP_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Group ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => Helper::COLUMN_NAME_PRODUCT_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Product ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => Helper::COLUMN_NAME_OPTION_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Option ID',
                ],
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => Helper::COLUMN_NAME_IS_CHANGED,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Is Changed Flag',
                ]
            ],


            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_OPTION_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_GROUP_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Group ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_TYPE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'default' => null,
                    'comment'   => 'Type',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_IS_REQUIRE,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '1',
                    'comment'   => 'Is Required',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_SKU,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 64,
                    'comment'   => 'SKU',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_MAX_CHARACTERS,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'comment'   => 'Max Characters',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_FILE_EXTENSION,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'comment'   => 'File Extension',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_IMAGE_SIZE_X,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'comment'   => 'Image Size X',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_IMAGE_SIZE_Y,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'comment'   => 'Image Size Y',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => Helper::COLUMN_NAME_SORT_ORDER,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Sort Order',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => Helper::COLUMN_NAME_OPTION_PRICE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option Price ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => Helper::COLUMN_NAME_OPTION_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Option ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => Helper::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => Helper::COLUMN_NAME_PRICE,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment'   => 'Price',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => Helper::COLUMN_NAME_PRICE_TYPE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 7,
                    'nullable' => false,
                    'default' => 'fixed',
                    'comment'   => 'Price Type',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TITLE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option Title ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => Helper::COLUMN_NAME_OPTION_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Option ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => Helper::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => Helper::COLUMN_NAME_TITLE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Title',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option Type ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'field_name' => Helper::COLUMN_NAME_OPTION_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Option ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'field_name' => Helper::COLUMN_NAME_SKU,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 64,
                    'comment'   => 'SKU',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'field_name' => Helper::COLUMN_NAME_SORT_ORDER,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Sort Order',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TYPE_PRICE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option Type Price ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Option Type ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => Helper::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => Helper::COLUMN_NAME_PRICE,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment'   => 'Price',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => Helper::COLUMN_NAME_PRICE_TYPE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 7,
                    'nullable' => false,
                    'default' => 'fixed',
                    'comment'   => 'Price Type',
                ]
            ],



            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TYPE_TITLE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Option Title ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Option ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => Helper::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => Helper::COLUMN_NAME_TITLE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Title',
                ]
            ],
        ];

        return $dataArray;
    }

    /**
     * Retrieve module indexes data array
     *
     * @return array
     */
    public function getIndexes()
    {
        $dataArray = [
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => [
                    Helper::COLUMN_NAME_GROUP_ID,
                    Helper::COLUMN_NAME_OPTION_ID,
                    Helper::COLUMN_NAME_PRODUCT_ID
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => [],
                'remove' => 'true'
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'field_name' => [
                    Helper::COLUMN_NAME_GROUP_ID,
                    Helper::COLUMN_NAME_PRODUCT_ID
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'field_name' => [
                    Helper::COLUMN_NAME_GROUP_ID
                ],
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => [
                    Helper::COLUMN_NAME_OPTION_ID,
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'field_name' => [
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => [
                    Helper::COLUMN_NAME_OPTION_ID,
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'field_name' => [
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'field_name' => [
                    Helper::COLUMN_NAME_OPTION_ID,
                ],
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => [
                    Helper::COLUMN_NAME_OPTION_TYPE_ID,
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'field_name' => [
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => [
                    Helper::COLUMN_NAME_OPTION_TYPE_ID,
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'field_name' => [
                    Helper::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => '',
                'options' => []
            ],
        ];

        return $dataArray;
    }

    /**
     * Retrieve module foreign keys data array
     *
     * @return array
     */
    public function getForeignKeys()
    {
        $dataArray = [
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'column_name' => Helper::COLUMN_NAME_GROUP_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP,
                'reference_column_name' => Helper::COLUMN_NAME_GROUP_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_RELATION,
                'column_name' => Helper::COLUMN_NAME_PRODUCT_ID,
                'reference_table_name' => 'catalog_product_entity',
                'reference_column_name' => 'entity_id',
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'column_name' => Helper::COLUMN_NAME_GROUP_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP,
                'reference_column_name' => Helper::COLUMN_NAME_GROUP_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'reference_column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_PRICE,
                'column_name' => Helper::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => 'store_id',
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'reference_column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TITLE,
                'column_name' => Helper::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => 'store_id',
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP_OPTION,
                'reference_column_name' => Helper::COLUMN_NAME_OPTION_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'column_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'reference_column_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_PRICE,
                'column_name' => Helper::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => 'store_id',
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'column_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'reference_table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_VALUE,
                'reference_column_name' => Helper::COLUMN_NAME_OPTION_TYPE_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => Helper::TABLE_NAME_GROUP_OPTION_TYPE_TITLE,
                'column_name' => Helper::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => 'store_id',
                'on_delete' => Table::ACTION_CASCADE
            ],
        ];

        return $dataArray;
    }
}
