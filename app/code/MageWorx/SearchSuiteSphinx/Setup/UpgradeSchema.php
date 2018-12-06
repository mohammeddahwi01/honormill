<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package MageWorx\LandingPagesPro\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            /**
             * Create table 'mageworx_catalogsearch_product_delta'
             */
            $tableLandingPage = $installer->getConnection()->newTable(
                $installer->getTable('mageworx_catalogsearch_product_delta')
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Entity ID'
            )->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Attribute ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Store ID'
            )->addColumn(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_catalogsearch_product_delta',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_catalogsearch_product_delta',
                    'entity_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_catalogsearch_product_delta',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )->addIndex(
                $installer->getIdxName(
                    'mageworx_catalogsearch_product_delta',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Products data for sphinx delta index');

            $installer->getConnection()->createTable($tableLandingPage);
        }

        $installer->endSetup();
    }
}
