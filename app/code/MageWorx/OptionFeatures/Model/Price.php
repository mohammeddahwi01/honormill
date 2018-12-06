<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class Price
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataObject
     */
    protected $specialPriceModel;

    /**
     * @var DataObject
     */
    protected $tierPriceModel;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DataObject $specialPriceModel
     * @param DataObject $tierPriceModel
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataObject $specialPriceModel,
        DataObject $tierPriceModel,
        BaseHelper $baseHelper
    ) {
        $this->productRepository = $productRepository;
        $this->specialPriceModel = $specialPriceModel;
        $this->tierPriceModel    = $tierPriceModel;
        $this->baseHelper        = $baseHelper;
    }

    /**
     * Get actual price using suitable special and tier prices
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @return float|null
     */
    public function getPrice($option, $value)
    {
        if (!$this->specialPriceModel && !$this->tierPriceModel) {
            return $value->getPrice(true);
        }

        $originalProduct = $option->getProduct();
        $infoBuyRequest = $this->baseHelper->getInfoBuyRequest($originalProduct);

        $valueQty = $this->getValueQty($option, $value, $infoBuyRequest);
        $productQty = !empty($infoBuyRequest['qty']) ? $infoBuyRequest['qty'] : 1;

        $originalProductOptions = $originalProduct->getData('options');
        foreach ($originalProductOptions as $originalProductOption) {
            $originalProductOptionValues = $originalProductOption->getValues();
            if (!empty($originalProductOptionValues[$value->getOptionTypeId()])) {
                $originalValue = $originalProductOptionValues[$value->getOptionTypeId()];
                break;
            }
        }
        if (empty($originalValue)) {
            return $value->getPrice(true);
        }

        $specialPrice         = $this->specialPriceModel->getActualSpecialPrice($originalValue);
        $tierPrices           = $this->tierPriceModel->getSuitableTierPrices($originalValue);
        $suitableTierPrice    = null;
        $suitableTierPriceQty = null;

        $isOneTime = $option->getData('one_time');
        if ($isOneTime) {
            $totalQty = $valueQty;
        } else {
            $totalQty = $productQty * $valueQty;
        }

        if (!isset($tierPrices[$totalQty])) {
            if (!is_array($tierPrices) || empty($tierPrices)) {
                $tierPrices = [];
            }
            foreach ($tierPrices as $tierPriceItemQty => $tierPriceItem) {
                if ($suitableTierPriceQty < $tierPriceItemQty && $totalQty >= $tierPriceItemQty) {
                    $suitableTierPrice    = $tierPriceItem;
                    $suitableTierPriceQty = $tierPriceItemQty;
                }
            }
        } else {
            $suitableTierPrice = $tierPrices[$totalQty];
        }

        if ($suitableTierPrice && ($suitableTierPrice['price'] < $specialPrice || $specialPrice === null)) {
            $price = $suitableTierPrice['price'];
        } else if ($specialPrice !== null) {
            $price = $specialPrice;
        } else {
            $price = $originalValue->getPrice(true);
        }

        return $price;
    }

    /**
     * Get selected value qty
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @param array $infoBuyRequest
     * @return float
     */
    protected function getValueQty($option, $value, $infoBuyRequest)
    {
        $valueQty = 1;
        if (!empty($infoBuyRequest['options_qty'][$option->getOptionId()][$value->getOptionTypeId()])) {
            $valueQty = $infoBuyRequest['options_qty'][$option->getOptionId()][$value->getOptionTypeId()];
        } elseif (!empty($infoBuyRequest['options_qty'][$option->getOptionId()])) {
            $valueQty = $infoBuyRequest['options_qty'][$option->getOptionId()];
        }
        return $valueQty;
    }
}
