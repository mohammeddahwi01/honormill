<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\DB\Ddl\Trigger;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const MAGEWORX_OPTION_ID = 'mageworx_option_id';
    const MAGEWORX_OPTION_TYPE_ID = 'mageworx_option_type_id';
    const CATALOG_PRODUCT_OPTION_TABLE = 'catalog_product_option';
    const CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE = 'catalog_product_option_type_value';

    /**
     * @var TriggerFactory
     */
    protected $triggerFactory;

    public function __construct(
        TriggerFactory $triggerFactory
    ) {
    
        $this->triggerFactory = $triggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            //add mageworx unique id for catalog_product_option table
            $installer->getConnection()->addColumn(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE),
                static::MAGEWORX_OPTION_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 40,
                    'nullable' => true,
                    'comment' => 'MageWorx Option Id (added by MageWorx Option Base)',
                    'after' => 'product_id'
                ]
            );

            $triggerName = 'insert_'.static::MAGEWORX_OPTION_ID;
            $event = 'INSERT';

            /** @var Trigger $optionTrigger */
            $optionTrigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE));

            $optionTrigger->addStatement($this->buildStatement($event, static::MAGEWORX_OPTION_ID));

            $setup->getConnection()->dropTrigger($optionTrigger->getName());
            $setup->getConnection()->createTrigger($optionTrigger);

            //add mageworx unique id for catalog_product_option_type_value table
            $installer->getConnection()->addColumn(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE),
                static::MAGEWORX_OPTION_TYPE_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 40,
                    'nullable' => true,
                    'comment' => 'MageWorx Option Type Id (added by MageWorx Option Base)',
                    'after' => 'option_id'
                ]
            );

            $triggerName = 'insert_'.static::MAGEWORX_OPTION_TYPE_ID;
            $event = 'INSERT';

            /** @var Trigger $optionTypeTrigger */
            $optionTypeTrigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE));

            $optionTypeTrigger->addStatement($this->buildStatement($event, static::MAGEWORX_OPTION_TYPE_ID));

            $setup->getConnection()->dropTrigger($optionTypeTrigger->getName());
            $setup->getConnection()->createTrigger($optionTypeTrigger);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE),
                $setup->getIdxName(
                    static::CATALOG_PRODUCT_OPTION_TABLE,
                    static::MAGEWORX_OPTION_ID,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                static::MAGEWORX_OPTION_ID,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );

            $setup->getConnection()->addIndex(
                $setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE),
                $setup->getIdxName(
                    static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE,
                    static::MAGEWORX_OPTION_TYPE_ID,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                static::MAGEWORX_OPTION_TYPE_ID,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'mageworx_is_require',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'MageWorx Is Required',
                ]
            );
        }

        $installer->endSetup();
    }

    protected function buildStatement($event, $fieldName)
    {
        switch ($event) {
            case Trigger::EVENT_INSERT:
                $triggerSql = "IF (NEW.".$fieldName." IS NULL) THEN SET NEW.".$fieldName." = UUID(); END IF;";
                return $triggerSql;
            default:
                return '';
        }
    }
}
