<?php
/**
 * Install script will add product attribute
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */

namespace Scommerce\CatalogUrl\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeCode = 'product_primary_category';
        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Primary Category',
                'input' => 'select',
                'class' => '',
                'source' => 'Scommerce\CatalogUrl\Model\Entity\Attribute\Source\Categories',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Primary Category',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
    }
}
