<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSwatches\Model;

use Magento\Framework\DB\Ddl\Table;
use MageWorx\OptionSwatches\Helper\Data as Helper;
use \MageWorx\OptionBase\Model\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
{
    const CATALOG_PRODUCT_OPTION_TABLE_NAME = 'catalog_product_option';

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
                'table_name' => self::CATALOG_PRODUCT_OPTION_TABLE_NAME,
                'field_name' => Helper::KEY_IS_SWATCH,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'comment'   => 'Is Swatch Flag (added by MageWorx Option Swatches)',
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
        return [];
    }

    /**
     * Retrieve module foreign keys data array
     *
     * @return array
     */
    public function getForeignKeys()
    {
        return [];
    }
}
