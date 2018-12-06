<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use MageWorx\OptionBase\Model\ValidatorInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class Validator implements ValidatorInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        BaseHelper $baseHelper
    ) {
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
        return $this->process($subject->getOption());
    }

    /**
     * Run validation process for cart and checkout
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Catalog\Api\Data\CustomOptionInterface $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option)
    {
        return $this->process($option);
    }

    /**
     * @param $option
     * @return bool
     */
    protected function process($option)
    {
        if (!empty($option['disabled'])) {
            return false;
        }

        $values = $option->getValues() ?: [];

        foreach ($values as $value) {
            if (!empty($value['disabled'])) {
                return false;
            }
        }

        return true;
    }
}