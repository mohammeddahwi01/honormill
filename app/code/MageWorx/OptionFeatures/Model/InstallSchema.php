<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Ddl\Table;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

class InstallSchema implements \MageWorx\OptionBase\Model\InstallSchemaInterface
{
    const CATALOG_PRODUCT_OPTION_TABLE_NAME = 'catalog_product_option';

    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    public function __construct(
        OptionBaseHelper $helper
    ) {
    
        $this->helper = $helper;
    }

    /**
     * Get module table prefix
     *
     * @return string
     */
    public function getModuleTablePrefix()
    {
        return 'mageworx_optionfeatures';
    }

    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            /* Table 'mageworx_optionfeatures_product_attributes' */
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => ProductAttributes::COLUMN_ENTITY_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment' => 'ID',
                ],
                'skip_add_to_template' => true
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => ProductAttributes::COLUMN_PRODUCT_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned'  => true,
                    'nullable' => false,
                    'default'   => 0,
                    'comment'   => 'Product ID',
                ],
                'skip_add_to_template' => true
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => Helper::KEY_ABSOLUTE_PRICE,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Absolute Price Flag',
                ]
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => Helper::KEY_ABSOLUTE_WEIGHT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Absolute Weight Flag',
                ]
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => Helper::KEY_ABSOLUTE_COST,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Absolute Cost Flag',
                ]
            ],
            /* Table 'catalog_product_option_type_value' */
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_IS_DEFAULT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Is Default Value Flag (added by MageWorx Option Features)',
                ],
                'drop' => true
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_COST,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'unsigned'  => false,
                    'nullable'  => true,
                    'comment'   => 'Cost (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_WEIGHT,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'unsigned'  => false,
                    'nullable'  => true,
                    'comment'   => 'Weight (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_DISABLED,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    'comment'   => 'Disabled (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_DISABLED,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    'comment'   => 'Disabled (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_QTY_INPUT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Qty Input Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_ONE_TIME,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'One Time Option Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_DESCRIPTION_ID,
                'params' => [
                    'type'      => Table::TYPE_INTEGER,
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'comment'   => 'Option Type Description ID',
                ]
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                'params' => [
                    'type'      => Table::TYPE_TEXT,
                    'length'    => 40,
                    'nullable'  => true,
                    'default'   => null,
                    'comment'   => 'MageWorx Option Type ID',
                ]
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_DESCRIPTION,
                'params' => [
                    'type'      => Table::TYPE_TEXT,
                    'nullable'  => true,
                    'default'   => null,
                    'comment'   => 'Description',
                ]
            ],
            //MageWorx OptionFeatures Option Type Image Table
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_OPTION_TYPE_IMAGE_ID,
                'params' => [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment' => 'ID',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '40',
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'MageWorx Option Type ID',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_MEDIA_TYPE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '32',
                    'nullable' => true,
                    'default' => 'image',
                    'comment' => 'Media Type',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_VALUE,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Value',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_TITLE_TEXT,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Title Text',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_SORT_ORDER,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Sort Order',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_BASE_IMAGE,
                'params' => [
                    'type' => Table::TYPE_BOOLEAN,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Base Image',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_TOOLTIP_IMAGE,
                'params' => [
                    'type' => Table::TYPE_BOOLEAN,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Tooltip Image',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_COLOR,
                'params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '6',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Color',
                ],
            ],
            [
                'table_name' => self::CATALOG_PRODUCT_OPTION_TABLE_NAME,
                'field_name' => Helper::KEY_OPTION_GALLERY_DISPLAY_MODE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => Helper::OPTION_GALLERY_TYPE_DISABLED,
                    'comment' => 'MageWorx option gallery display type (added by MageWorx_OptionFeatures)',
                ]
            ],
            [
                'table_name' => self::CATALOG_PRODUCT_OPTION_TABLE_NAME,
                'field_name' => Helper::KEY_OPTION_IMAGE_MODE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Option image mode (added by MageWorx_OptionFeatures)',
                ]
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_REPLACE_MAIN_GALLERY_IMAGE,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment' => 'Use for replacement of the main image in the product gallery (product view page)',
                ],
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_HIDE_IN_GALLERY,
                'params' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Should be displayed this image in the Option Gallery or not?',
                ],
            ],
            //MageWorx Option Description Table
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_OPTION_DESCRIPTION_ID,
                'params' => [
                    'type'      => Table::TYPE_INTEGER,
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'comment'   => 'Option Description ID',
                ]
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID,
                'params' => [
                    'type'      => Table::TYPE_TEXT,
                    'length'    => 40,
                    'nullable'  => true,
                    'default'   => null,
                    'comment'   => 'MageWorx Option ID',
                ]
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_DESCRIPTION,
                'params' => [
                    'type'      => Table::TYPE_TEXT,
                    'nullable'  => true,
                    'default'   => null,
                    'comment'   => 'Description',
                ]
            ],
            //MageWorx Option Value Is Default Table
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'field_name' => OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_IS_DEFAULT_ID,
                'params' => [
                    'type'      => Table::TYPE_INTEGER,
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'comment'   => 'Option Is Default ID',
                ]
            ],
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'field_name' => OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                'params' => [
                    'type'      => Table::TYPE_TEXT,
                    'length'    => 40,
                    'nullable'  => true,
                    'default'   => null,
                    'comment'   => 'MageWorx Option Type ID',
                ]
            ],
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'field_name' => OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    'comment'   => 'Store ID',
                ]
            ],
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'field_name' => OptionTypeIsDefault::COLUMN_NAME_IS_DEFAULT,
                'params' => [
                    'type'      => Table::TYPE_SMALLINT,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '1',
                    'comment'   => 'Is Default',
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
        $dataArray = [
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => ProductAttributes::COLUMN_PRODUCT_ID,
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'field_name' => ProductAttributes::COLUMN_PRODUCT_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_STORE_ID,
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_STORE_ID,
                'index_type' => '',
                'options' => []
            ],
            [
                'table_name' => Image::TABLE_NAME,
                'field_name' => Image::COLUMN_MAGEWORX_OPTION_TYPE_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_INDEX,
                'options' => []
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => [],
                'drop' => true
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID,
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => [],
                'drop' => true
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'field_name' => [
                    OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                    OptionTypeDescription::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'field_name' => [
                    OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID,
                    OptionDescription::COLUMN_NAME_STORE_ID
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
                'options' => []
            ],
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'field_name' => [
                    OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID,
                    OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                ],
                'index_type' => AdapterInterface::INDEX_TYPE_UNIQUE,
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
                'table_name' => ProductAttributes::TABLE_NAME,
                'column_name' => ProductAttributes::COLUMN_PRODUCT_ID,
                'reference_table_name' => 'catalog_product_entity',
                'reference_column_name' => $this->helper->getLinkField(ProductInterface::class),
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => OptionTypeDescription::TABLE_NAME,
                'column_name' => OptionTypeDescription::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => OptionTypeDescription::COLUMN_NAME_STORE_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => OptionDescription::TABLE_NAME,
                'column_name' => OptionDescription::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => OptionDescription::COLUMN_NAME_STORE_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'column_name' => 'product_id',
                'reference_table_name' => 'catalog_product_entity',
                'reference_column_name' => 'entity_id',
                'on_delete' => Table::ACTION_CASCADE,
                'remove' => true
            ],
            [
                'table_name' => ProductAttributes::TABLE_NAME,
                'column_name' => 'product_id',
                'reference_table_name' => 'catalog_product_entity',
                'reference_column_name' => $this->helper->isEnterprise() ? 'row_id' : 'entity_id',
                'on_delete' => Table::ACTION_CASCADE,
                'callback' => [
                    'clearUnusedData' => [
                        'field1' => 'product_id',
                        'field2' => $this->helper->isEnterprise() ? 'row_id' : 'entity_id',
                        'table1' => 'mageworx_optionfeatures_product_attributes',
                        'table2' => 'catalog_product_entity'
                    ]
                ]
            ],
            [
                'table_name' => OptionTypeIsDefault::TABLE_NAME,
                'column_name' => OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                'reference_table_name' => 'store',
                'reference_column_name' => OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                'on_delete' => Table::ACTION_CASCADE
            ],
        ];

        return $dataArray;
    }
}
