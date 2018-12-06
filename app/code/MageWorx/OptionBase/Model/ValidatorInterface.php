<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;

interface ValidatorInterface
{
    /**
     * Validation for option on add to cart action
     *
     * @param DefaultType $subject
     * @param array $values
     * @return bool
     */
    public function canValidateAddToCart($subject, $values);

    /**
     * Validation for option on cart and checkout
     *
     * @param ProductInterface $product
     * @param CustomOptionInterface $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option);
}
