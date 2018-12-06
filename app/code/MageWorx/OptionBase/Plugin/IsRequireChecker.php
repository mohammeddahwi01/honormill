<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin;

use MageWorx\OptionBase\Model\ResourceModel\DataSaver;
use MageWorx\OptionBase\Model\ValidationResolver;

class IsRequireChecker
{
    /**
     * @var DataSaver
     */
    protected $dataSaver;

    /**
     * @var ValidationResolver
     */
    protected $validationResolver;

    /**
     * IsRequireChecker constructor.
     *
     * @param ValidationResolver $validationResolver
     * @param DataSaver $dataSaver
     */
    public function __construct(
        ValidationResolver $validationResolver,
        DataSaver $dataSaver
    ) {
        $this->validationResolver = $validationResolver;
        $this->dataSaver          = $dataSaver;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function afterAfterSave(
        \Magento\Catalog\Model\Product $subject,
        $product
    ) {
        $options          = $product->getOptions();
        $isRequireOptions = false;

        if (!$options) {
            $this->dataSaver->updateValueIsRequire($product->getId(), (int)$isRequireOptions);
            return $product;
        }

        foreach ($options as $option) {
            if (!$option->getIsRequire()) {
                continue;
            }
            //prepare data
            if (is_null($option->getValues()) && !is_null($option->getData('values'))) {
                $option->setValues($option->getData('values'));
            }
            $optionRequireStatus = true;
            /* @var $validatorItem \MageWorx\OptionBase\Model\ValidatorInterface */
            foreach ($this->validationResolver->getValidators() as $key => $validatorItem) {
                if (!$validatorItem->canValidateCartCheckout($product, $option)) {
                    $optionRequireStatus = false;
                    break;
                }
            }

            if ($optionRequireStatus) {
                $isRequireOptions = true;
                break;
            }
        }

        $this->dataSaver->updateValueIsRequire($product->getId(), (int)$isRequireOptions);

        return $product;
    }
}