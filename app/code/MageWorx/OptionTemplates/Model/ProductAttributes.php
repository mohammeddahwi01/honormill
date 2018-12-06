<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model;

use MageWorx\OptionBase\Model\Product\Attributes as ProductAttributesEntity;

class ProductAttributes
{
    /**
     * @var ProductAttributesEntity
     */
    protected $productAttributes;

    /**
     * @param ProductAttributesEntity $productAttributes
     */
    public function __construct(
        ProductAttributesEntity $productAttributes
    ) {
        $this->productAttributes = $productAttributes;
    }

    /**
     * Get product attributes from group
     *
     * @param Group $group
     * @return array
     */
    public function getProductAttributesFromGroup($group)
    {
        $keys = [];
        $productAttributes = $this->productAttributes->getData();
        foreach ($productAttributes as $productAttribute) {
            foreach ($productAttribute->getKeys() as $productAttributeKey) {
                $keys[] = $productAttributeKey;
            }
        }

        $attributes = [];
        foreach ($keys as $key) {
            $attributes[$key] = $group->getData($key);
        }
        return $attributes;
    }
}
