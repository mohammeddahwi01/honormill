<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Plugin;

use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use MageWorx\OptionFeatures\Model\Price as AdvancedPricingPrice;

class AroundGetOptionPrice
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var BasePriceHelper
     */
    protected $basePriceHelper;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var AdvancedPricingPrice
     */
    protected $advancedPricingPrice;

    /**
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param BasePriceHelper $basePriceHelper
     * @param TaxHelper $taxHelper
     * @param StoreManager $storeManager
     * @param PricingHelper $pricingHelper
     * @param AdvancedPricingPrice $advancedPricingPrice
     */
    public function __construct(
        Helper $helper,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        TaxHelper $taxHelper,
        StoreManager $storeManager,
        AdvancedPricingPrice $advancedPricingPrice,
        PricingHelper $pricingHelper
    ) {
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->basePriceHelper = $basePriceHelper;
        $this->taxHelper = $taxHelper;
        $this->storeManager = $storeManager;
        $this->advancedPricingPrice = $advancedPricingPrice;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param \Magento\Catalog\Model\Product\Option\Type\DefaultType $subject
     * @param callable $proceed
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function aroundGetEditableOptionValue($subject, $proceed, $optionValue)
    {
        $option = $subject->getOption();
        $result = '';

        $optionsQty = $this->getBuyRequestOptionsQty($subject);

        if (!$this->isSingleSelection($option)) {
            foreach (explode(',', $optionValue) as $_value) {
                $_result = $option->getValueById($_value);
                if ($_result) {
                    $optionQty = $this->getOptionQty($optionsQty, $option, $_value);
                    $titleQty = $this->getTitleQty($subject, $optionQty);
                    $result .= $this->setTitle($_result, $titleQty, 0);
                } else {
                    if ($subject->getListener()) {
                        $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                        $result = '';
                        break;
                    }
                }
            }
            $result = substr($result, 0, -2);
        } elseif ($this->isSingleSelection($option)) {
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $optionQty = $this->getOptionQty($optionsQty, $option, $optionValue);
                $titleQty = $this->getTitleQty($subject, $optionQty);
                $result .= $this->setTitle($_result, $titleQty, 1);
            } else {
                if ($subject->getListener()) {
                    $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                }
                $result = '';
            }
        } else {
            $result = $optionValue;
        }
        return $result;
    }

    /**
     * Return Price for selected option
     * @param \Magento\Catalog\Model\Product\Option\Type\DefaultType $subject
     * @param callable $proceed
     * @param string $optionValue Prepared for cart option value
     * @param float $basePrice For percent price type
     * @return float
     */
    public function aroundGetOptionPrice($subject, $proceed, $optionValue, $basePrice)
    {
        $option = $subject->getOption();
        $result = 0;

        $optionsQty = $this->getBuyRequestOptionsQty($subject);

        if (!$this->isSingleSelection($option)) {
            foreach (explode(',', $optionValue) as $value) {
                $qty = $this->getOptionQty($optionsQty, $option, $value);
                $_result = $option->getValueById($value);
                if ($_result) {
                    $result += $this->getChargableOptionPrice(
                        $this->advancedPricingPrice->getPrice($option, $_result),
                        $qty
                    );
                } else {
                    if ($subject->getListener()) {
                        $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                        break;
                    }
                }
            }
        } elseif ($this->isSingleSelection($option)) {
            $qty = $this->getOptionQty($optionsQty, $option, $optionValue);
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $result = $this->getChargableOptionPrice(
                    $this->advancedPricingPrice->getPrice($option, $_result),
                    $qty
                );
            } else {
                if ($subject->getListener()) {
                    $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                }
            }
        }

        return $result;
    }

    protected function getBuyRequestOptionsQty($subject)
    {
        $optionsQty = [];
        $configurationItemOption = $subject->getConfigurationItemOption();
        if ($configurationItemOption) {
            $quoteItem = $configurationItemOption->getItem();
            if ($quoteItem) {
                $buyRequest = $quoteItem->getBuyRequest();
                if ($buyRequest) {
                    $optionsQty = $buyRequest->getOptionsQty();
                }
            }
        }
        return $optionsQty;
    }

    protected function getOptionQty($optionsQty, $option, $optionValue)
    {
        $qty = 1;
        if (isset($optionsQty[$option->getOptionId()])) {
            if (!is_array($optionsQty[$option->getOptionId()])) {
                $qty = $optionsQty[$option->getOptionId()];
            } else {
                if (isset($optionsQty[$option->getOptionId()][$optionValue])) {
                    $qty = $optionsQty[$option->getOptionId()][$optionValue];
                }
            }
        }
        return $qty;
    }

    protected function getTitleQty($subject, $optionQty)
    {
        $productQty = $subject->getConfigurationItemOption()->getItem()->getQty();
        if ($subject->getOption()->getOneTime()) {
            $titleQty = $optionQty;
        } else {
            $titleQty = $optionQty * $productQty;
        }
        return $titleQty;
    }

    /**
     * Set extended title for option
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $model
     * @param integer $qty
     * @param bool $isSingleSelection
     * @return string
     */
    protected function setTitle($model, $qty, $isSingleSelection)
    {
        $title = '';
        if ($qty > 1) {
            $title .= $qty .' x ';
        }
        $title .= $model->getTitle();
        $title .= $this->getOptionPriceAsString($model, $qty);
        if (!$isSingleSelection) {
            $title .=  ', ';
        }
        return $title;
    }

    /**
     * Get extended option price as string
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $model
     * @param integer $qty
     * @return string
     */
    protected function getOptionPriceAsString($model, $qty)
    {
        $actualPrice = null;
        if ($model instanceof \Magento\Catalog\Model\Product\Option\Value) {
            $product = $model->getOption()->getProduct();
            $actualPrice = $this->advancedPricingPrice->getPrice(
                $model->getOption(),
                $model
            );
        } elseif ($model instanceof \Magento\Catalog\Model\Product\Option) {
            $product = $model->getProduct();
        } else {
            return '';
        }

        if ($actualPrice !== null) {
            $price = $actualPrice;
        } else {
            $price = $model->getPriceType() == 'percent' ?
                $price = $product->getPrice() * $model->getPrice() / 100 :
                $model->getPrice();
        }
        $price *= $qty;

        if (!$price || $price < 0) {
            return '';
        }

        $store = $product->getStore();

        $priceExclTax = $this->basePriceHelper->getTaxPrice($product, $price, false);
        $priceInclTax = $this->basePriceHelper->getTaxPrice($product, $price, true);

        // show exclude tax
        if ($this->taxHelper->displayCartPriceExclTax($store)) {
            return ' - ' . $this->pricingHelper->currencyByStore($priceExclTax, $store, true, false);
        }

        // show exclude and include tax
        if ($this->taxHelper->displayCartBothPrices($store)) {
            return ' - ' .
                $this->pricingHelper->currencyByStore($priceExclTax, $store, true, false) .
                ' ' .
                __('(Incl. Tax') .
                ' ' .
                $this->pricingHelper->currencyByStore($priceInclTax, $store, true, false) .
                ')';
        }

        // show include tax
        if ($this->taxHelper->displayCartPriceInclTax($store)) {
            return ' - ' . $this->pricingHelper->currencyByStore($priceInclTax, $store, true, false);
        }
    }

    /**
     * Get currency symbol from config
     *
     * @return string
     */
    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * Check if option has single or multiple values selection
     *
     * @return boolean
     */
    protected function isSingleSelection($option)
    {
        $single = [
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN,
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO,
        ];
        return in_array($option->getType(), $single);
    }

    /**
     * Return final chargable price for option
     *
     * @param float $price Price of option
     * @param float $qty Option/option value quantity
     * @return float
     */
    protected function getChargableOptionPrice($price, $qty)
    {
        return $price * $qty;
    }

    /**
     * Return currently unavailable product configuration message
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getWrongConfigurationMessage()
    {
        return __('Some of the selected item options are not currently available.');
    }
}
