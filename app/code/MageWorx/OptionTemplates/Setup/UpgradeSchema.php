<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionTemplates\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\DB\Ddl\Trigger;
use MageWorx\OptionTemplates\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Installer;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const MAGEWORX_OPTION_ID = 'mageworx_option_id';
    const MAGEWORX_OPTION_TYPE_ID = 'mageworx_option_type_id';
    const MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE = 'mageworx_optiontemplates_group_option';
    const MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE = 'mageworx_optiontemplates_group_option_type_value';

    /**
     * @var TriggerFactory
     */
    protected $triggerFactory;

    /**
     * @var Installer
     */
    protected $optionBaseInstaller;

    public function __construct(
        Installer $optionBaseInstaller,
        TriggerFactory $triggerFactory
    ) {
        $this->triggerFactory = $triggerFactory;
        $this->optionBaseInstaller = $optionBaseInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $this->optionBaseInstaller->install();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            //add mageworx unique id for catalog_product_option table
            $installer->getConnection()->addColumn(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE),
                static::MAGEWORX_OPTION_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 40,
                    'nullable' => true,
                    'comment' => 'MageWorx Option Id',
                    'after' => 'option_id'
                ]
            );

            $triggerName = 'insert_template_'.static::MAGEWORX_OPTION_ID;
            $event = 'INSERT';

            /** @var Trigger $optionTrigger */
            $optionTrigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE));

            $optionTrigger->addStatement($this->buildStatement($event, static::MAGEWORX_OPTION_ID));

            $setup->getConnection()->dropTrigger($optionTrigger->getName());
            $setup->getConnection()->createTrigger($optionTrigger);

            //add mageworx unique id for catalog_product_option_type_value table
            $installer->getConnection()->addColumn(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE),
                static::MAGEWORX_OPTION_TYPE_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 40,
                    'nullable' => true,
                    'comment' => 'MageWorx Option Type Id',
                    'after' => 'option_id'
                ]
            );

            $triggerName = 'insert_template_'.static::MAGEWORX_OPTION_TYPE_ID;
            $event = 'INSERT';

            /** @var Trigger $optionTypeTrigger */
            $optionTypeTrigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE));

            $optionTypeTrigger->addStatement($this->buildStatement($event, static::MAGEWORX_OPTION_TYPE_ID));

            $setup->getConnection()->dropTrigger($optionTypeTrigger->getName());
            $setup->getConnection()->createTrigger($optionTypeTrigger);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE),
                $setup->getIdxName(
                    static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TABLE,
                    static::MAGEWORX_OPTION_ID,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                static::MAGEWORX_OPTION_ID,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );

            $setup->getConnection()->addIndex(
                $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE),
                $setup->getIdxName(
                    static::MAGEWORX_OPTIONTEMPLATES_GROUP_TYPE_VALUE_TABLE,
                    static::MAGEWORX_OPTION_TYPE_ID,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                static::MAGEWORX_OPTION_TYPE_ID,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optiontemplates/hide_all_options'],
                "path = 'mageworx_optiontemplates/main/hide_all_options'"
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $setup->getConnection()->update(
                $setup->getTable(Helper::TABLE_NAME_RELATION),
                [Helper::COLUMN_NAME_IS_CHANGED => true],
                "1"
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
