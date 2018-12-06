<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model;

use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use MageWorx\OptionDependency\Model\Config;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \MageWorx\OptionBase\Model\InstallSchemaInterface
{
    /**
     * @var OptionBaseHelper
     */
    protected $helperBase;

    /**
     * @param OptionBaseHelper $helperBase
     */
    public function __construct(
        OptionBaseHelper $helperBase
    ) {
    
        $this->helperBase = $helperBase;
    }

    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return 'mageworx_option_dependency';
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
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_DEPENDENCY_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Dependency Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_CHILD_OPTION_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Child Option Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_CHILD_OPTION_TYPE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Child Option Type Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_PARENT_OPTION_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Parent Option Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_PARENT_OPTION_TYPE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Parent Option Type Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_PRODUCT_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'default' => '0',
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Product Id',
                ]
            ],
            [
                'table_name' => Config::TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_GROUP_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment'   => 'Group Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_DEPENDENCY_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment'   => 'Dependency Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_CHILD_OPTION_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Child Option Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_CHILD_OPTION_TYPE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Child Option Type Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_PARENT_OPTION_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Parent Option Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_PARENT_OPTION_TYPE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Parent Option Type Id',
                ]
            ],
            [
                'table_name' => Config::OPTIONTEMPLATES_TABLE_NAME,
                'field_name' => Config::COLUMN_NAME_GROUP_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'default' => '0',
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Group Id',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Config::COLUMN_NAME_OPTION_TYPE_TITLE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Option Type Title Identifier (added by MageWorx Option Dependency)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Config::COLUMN_NAME_OPTION_TITLE_ID,
                'params' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment'   => 'Option Title Identifier (added by MageWorx Option Dependency)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Config::COLUMN_NAME_OPTION_DEPENDENCY_TYPE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment'   => 'Option Type Dependency Type (added by MageWorx Option Dependency)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Config::COLUMN_NAME_OPTION_DEPENDENCY_TYPE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment'   => 'Option Dependency Type (added by MageWorx Option Dependency)',
                ]
            ]
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
        $dataArray = [];

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
                'table_name' => Config::TABLE_NAME,
                'column_name' => 'product_id',
                'reference_table_name' => 'catalog_product_entity',
                'reference_column_name' => $this->helperBase->isEnterprise() ? 'row_id' : 'entity_id',
                'on_delete' => Table::ACTION_CASCADE,
                'callback' => [
                    'clearUnusedData' => [
                        'field1' => 'product_id',
                        'field2' => $this->helperBase->isEnterprise() ? 'row_id' : 'entity_id',
                        'table1' => 'mageworx_option_dependency',
                        'table2' => 'catalog_product_entity'
                    ]
                ]
            ],
            [
                'table_name' => 'mageworx_optiontemplates_group_option_dependency',
                'column_name' => 'group_id',
                'reference_table_name' => 'mageworx_optiontemplates_group',
                'reference_column_name' => 'group_id',
                'on_delete' => Table::ACTION_CASCADE,
                'callback' => [
                    'clearUnusedData' => [
                        'field1' => 'group_id',
                        'field2' => 'group_id',
                        'table1' => 'mageworx_optiontemplates_group_option_dependency',
                        'table2' => 'mageworx_optiontemplates_group'
                    ]
                ]
            ]
        ];

        return $dataArray;
    }
}
