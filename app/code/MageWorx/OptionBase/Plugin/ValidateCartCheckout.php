<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin;

use MageWorx\OptionBase\Model\ValidationResolver;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Catalog\Model\Product\Type\AbstractType;

class ValidateCartCheckout
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var ValidationResolver
     */
    protected $validationResolver;

    /**
     * @param ValidationResolver $validationResolver
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        ValidationResolver $validationResolver,
        BaseHelper $baseHelper
    ) {
        $this->validationResolver = $validationResolver;
        $this->baseHelper = $baseHelper;
    }

    /**
     * Check custom conditions to allow validate options on cart and checkout
     *
     * @param AbstractType $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCheckProductBuyState(AbstractType $subject, \Closure $proceed, $product)
    {
        if (!$product->getSkipCheckRequiredOption() && $product->getHasOptions()) {
            $options = $product->getProductOptionsCollection();
            foreach ($options as $option) {
                if ($option->getIsRequire() && $this->hasValidationPermission($product, $option)) {
                    $customOption = $product->getCustomOption($subject::OPTION_PREFIX . $option->getId());
                    if (!$customOption || strlen($customOption->getValue()) == 0) {
                        $product->setSkipCheckRequiredOption(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product has required options.')
                        );
                    }
                }
            }
        }

        return [$product];
    }

    /**
     * Check validation permission from APO modules
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return bool
     */
    protected function hasValidationPermission($product, $option)
    {
        if (!$this->validationResolver->getValidators()) {
            return true;
        }

        /* @var $validatorItem \MageWorx\OptionBase\Model\ValidatorInterface */
        foreach ($this->validationResolver->getValidators() as $validatorItem) {
            if (!$validatorItem->canValidateCartCheckout($product, $option)) {
                return false;
            }
        }
        return true;
    }
}

