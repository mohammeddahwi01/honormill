<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model;

use MageWorx\OptionBase\Model\ValidatorInterface;
use MageWorx\OptionDependency\Model\Config;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class Validator implements ValidatorInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var Config
     */
    protected $modelConfig;

    /**
     * @param Config $modelConfig
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        Config $modelConfig,
        BaseHelper $baseHelper
    ) {
        $this->modelConfig = $modelConfig;
        $this->baseHelper = $baseHelper;
    }

    /**
     * Run validation process for add to cart action
     *
     * @param DefaultType $subject
     * @param array $values
     * @return bool
     */
    public function canValidateAddToCart($subject, $values)
    {
        return $this->process($subject->getProduct(), $subject->getOption(), $values);
    }

    /**
     * Run validation process for cart and checkout
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option)
    {
        $value = $this->baseHelper->getInfoBuyRequest($product);
        $values = isset($value['options']) ? $value['options'] : [];

        return $this->process($product, $option, $values);
    }

    /**
     * Check dependent option, if hidden - skip validation
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function process($product, $option, $values)
    {
        $productId = $this->baseHelper->isEnterprise() ?
            $product->getRowId() :
            $product->getId();

        $isNeedValidation = $this->modelConfig->isNeedDependentOptionValidation(
            $option,
            $values,
            $product,
            $productId
        );

        return $isNeedValidation;
    }
}
