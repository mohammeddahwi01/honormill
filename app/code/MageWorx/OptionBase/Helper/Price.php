<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Tax\Model\Config as TaxHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;

class Price extends AbstractHelper
{
    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var TaxHelper
     */
    protected $taxConfig;

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManager $storeManager
     * @param TaxHelper $taxConfig
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManager $storeManager,
        TaxHelper $taxConfig,
        CatalogHelper $catalogHelper
    ) {
        $this->storeManager = $storeManager;
        $this->catalogHelper = $catalogHelper;
        $this->taxConfig = $taxConfig;
        parent::__construct($context);
    }

    /**
     * Get type of price display from the tax config
     * Returns 1 - without tax, 2 - with tax, 3 - both
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return integer
     */
    public function getPriceDisplayMode($store = null)
    {
        return $this->taxConfig->getPriceDisplayType($this->storeManager->getStore($store));
    }

    /**
     * Get flag: is catalog price already contains tax
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function getCatalogPriceContainsTax($store = null)
    {
        return $this->taxConfig->priceIncludesTax($this->storeManager->getStore($store));
    }

    /**
     * Get price using method for products to calculate tax price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float $price
     * @param bool|null $includeTax
     * @return float
     */
    public function getTaxPrice($product, $price, $includeTax = null)
    {
        //trigger calculation in any way if $includeTax flag is set
        if ($includeTax !== null) {
            $needUseShippingExcludeTax = $this->taxConfig->getNeedUseShippingExcludeTax();
            $this->taxConfig->setNeedUseShippingExcludeTax(true);
        }
        $price = $this->catalogHelper->getTaxPrice(
            $product,
            $price,
            $includeTax,
            null,
            null,
            null,
            null,
            null,
            true
        );
        if (isset($needUseShippingExcludeTax)) {
            $this->taxConfig->setNeedUseShippingExcludeTax($needUseShippingExcludeTax);
        }
        return $price;
    }
}
