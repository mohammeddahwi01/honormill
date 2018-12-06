<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Block\Product\View;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;

class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var BasePriceHelper
     */
    protected $basePriceHelper;

    /**
     * Options constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param PriceCurrencyInterface $priceCurrency
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param BaseHelper $baseHelper
     * @param BasePriceHelper $basePriceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\Format $localeFormat,
        PriceCurrencyInterface $priceCurrency,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $jsonEncoder,
            $option,
            $registry,
            $arrayUtils,
            $data
        );
        $this->localeFormat = $localeFormat;
        $this->priceCurrency = $priceCurrency;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->baseHelper = $baseHelper;
        $this->basePriceHelper = $basePriceHelper;
    }

    /**
     * Get product data
     *
     * @return string (JSON)
     */
    public function getProductJsonConfig()
    {
        $product = $this->getProduct();
        $productData = $product->getData();

        foreach ($productData as $key => $value) {
            if (is_string($value)) {
                $productData[$key] = str_replace("'", "`", $value);
            }
        }

        $productData['regular_price_excl_tax'] = $this->priceCurrency->convert($this->getProductRegularPrice(false));
        $productData['regular_price_incl_tax'] = $this->priceCurrency->convert($this->getProductRegularPrice(true));
        $productData['final_price_excl_tax'] = $this->priceCurrency->convert($this->getProductFinalPrice(false));
        $productData['final_price_incl_tax'] = $this->priceCurrency->convert($this->getProductFinalPrice(true));

        if (!empty($productData['price'])) {
            $productData['price'] = $this->priceCurrency->convert($productData['price']);
        }

        $r = $this->_jsonEncoder->encode($productData);

        return $r;
    }

    /**
     * @return string (JSON)
     */
    public function getLocalePriceFormat()
    {
        $data = $this->localeFormat->getPriceFormat();
        $data['priceSymbol'] = $this->priceCurrency->getCurrency()->getCurrencySymbol();

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * @param null $includeTax
     * @return float
     */
    public function getProductFinalPrice($includeTax = null)
    {
        $product = $this->getProduct();
        return $this->basePriceHelper->getTaxPrice($product, $product->getFinalPrice(), $includeTax);
    }

    /**
     * @param null $includeTax
     * @return float
     */
    public function getProductRegularPrice($includeTax = null)
    {
        $product = $this->getProduct();
        return $this->basePriceHelper->getTaxPrice($product, $product->getPrice(), $includeTax);
    }

    /**
     * Get type of price display from the tax config
     * Returns 1 - without tax, 2 - with tax, 3 - both
     *
     * @return integer
     */
    public function getPriceDisplayMode()
    {
        return $this->basePriceHelper->getPriceDisplayMode();
    }

    /**
     * Get flag: is catalog price already contains tax
     *
     * @return int
     */
    public function getCatalogPriceContainsTax()
    {
        return $this->basePriceHelper->getCatalogPriceContainsTax();
    }

    /**
     * Store options data in another config,
     * because if we add options data to the main config it generates fatal errors
     *
     * @return string {JSON}
     */
    public function getExtendedOptionsConfig()
    {
        $config = [];
        $product = $this->getProduct();
        $optionAttributes = $this->optionAttributes->getData();
        $optionValueAttributes = $this->optionValueAttributes->getData();
        /** @var \Magento\Catalog\Model\Product\Option $option */
        if (empty($product->getOptions())) {
            return $this->_jsonEncoder->encode($config);
        }
        foreach ($product->getOptions() as $option) {
            foreach ($optionAttributes as $optionAttribute) {
                $preparedData = $optionAttribute->prepareDataForFrontend($option);
                if (empty($preparedData) || !is_array($preparedData)) {
                    continue;
                }
                foreach ($preparedData as $preparedDataKey => $preparedDataValue) {
                    $config[$option->getId()][$preparedDataKey] = $preparedDataValue;
                }
            }
            /** @var \Magento\Catalog\Model\Product\Option\Value $value */
            if (empty($option->getValues())) {
                continue;
            }
            foreach ($option->getValues() as $value) {
                foreach ($optionValueAttributes as $optionValueAttribute) {
                    $preparedData = $optionValueAttribute->prepareDataForFrontend($value);
                    if (empty($preparedData) || !is_array($preparedData)) {
                        continue;
                    }
                    foreach ($preparedData as $preparedDataKey => $preparedDataValue) {
                        $config[$option->getId()]['values'][$value->getId()][$preparedDataKey] = $preparedDataValue;
                    }
                }

                $config[$option->getId()]['values'][$value->getId()]['title'] = $value->getTitle();
            }
        }

        return $this->_jsonEncoder->encode($config);
    }
}
