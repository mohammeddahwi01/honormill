<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use MageWorx\OptionFeatures\Model\ProductAttributes;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;
use MageWorx\OptionFeatures\Model\OptionDescription;

class Uninstall implements UninstallInterface
{
    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $connection->dropTable($connection->getTableName(ProductAttributes::TABLE_NAME));
        $connection->dropTable($connection->getTableName(OptionDescription::TABLE_NAME));
        $connection->dropTable($connection->getTableName(OptionTypeDescription::TABLE_NAME));

        $setup->endSetup();
    }
}
