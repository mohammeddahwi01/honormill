<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optioninventory/display_option_inventory_on_frontend'],
                "path = 'mageworx_optioninventory/optioninventory_main/display_option_inventory_on_frontend'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optioninventory/disable_out_of_stock_options'],
                "path = 'mageworx_optioninventory/optioninventory_main/disable_out_of_stock_options'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optioninventory/display_out_of_stock_message'],
                "path = 'mageworx_optioninventory/optioninventory_main/display_out_of_stock_message'"
            );
        }
    }
}
