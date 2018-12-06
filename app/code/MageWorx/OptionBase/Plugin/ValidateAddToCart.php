<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin;

use MageWorx\OptionBase\Model\ValidationResolver;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;

class ValidateAddToCart
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
     * Check custom conditions to allow validate options on add to cart action
     *
     * @param DefaultType $subject
     * @param array $values
     * @return array
     */
    public function beforeValidateUserValue(DefaultType $subject, $values)
    {
        $option = $subject->getOption();

        if (!$option->getIsRequire()) {
            return [$values];
        }

        if (!$this->validationResolver->getValidators()) {
            return [$values];
        }

        /* @var $validatorItem \MageWorx\OptionBase\Model\ValidatorInterface */
        foreach ($this->validationResolver->getValidators() as $validatorItem) {
            if (!$validatorItem->canValidateAddToCart($subject, $values)) {
                $option->setIsRequire(false);
                return [$values];
            }
        }

        return [$values];
    }
}
