<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\ProductAttributes;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var \MageWorx\OptionBase\Model\Installer
     */
    protected $optionBaseInstaller;

    /**
     * InstallSchema constructor.
     * @param \MageWorx\OptionBase\Model\Installer $optionBaseInstaller
     * @param \MageWorx\OptionBase\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\OptionBase\Model\Installer $optionBaseInstaller,
        OptionBaseHelper $helper
    ) {
        $this->optionBaseInstaller = $optionBaseInstaller;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->optionBaseInstaller->install();
        $setup->endSetup();
    }
}
