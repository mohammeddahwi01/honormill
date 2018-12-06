<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

    /**
     * UpgradeSchema constructor.
     *
     * @param \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
     */
    public function __construct(
        \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
    ) {
        $this->optionBaseInstaller = $optionBaseInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_weight'],
                "path = 'mageworx_optionfeatures/main/use_weight'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_weight'],
                "path = 'mageworx_optionfeatures/main/use_absolute_weight'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_cost'],
                "path = 'mageworx_optionfeatures/main/use_cost'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_cost'],
                "path = 'mageworx_optionfeatures/main/use_absolute_cost'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_absolute_price'],
                "path = 'mageworx_optionfeatures/main/use_absolute_price'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_one_time'],
                "path = 'mageworx_optionfeatures/main/use_one_time'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_qty_input'],
                "path = 'mageworx_optionfeatures/main/use_qty_input'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_description'],
                "path = 'mageworx_optionfeatures/main/use_description'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_option_description'],
                "path = 'mageworx_optionfeatures/main/use_option_description'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/use_is_default'],
                "path = 'mageworx_optionfeatures/main/use_is_default'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/base_image_thumbnail_size'],
                "path = 'mageworx_optionfeatures/main/base_image_thumbnail_size'"
            );
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => 'mageworx_apo/optionfeatures/tooltip_image_thumbnail_size'],
                "path = 'mageworx_optionfeatures/main/tooltip_image_thumbnail_size'"
            );
        }

        $this->optionBaseInstaller->install();
    }
}
