<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\InlineEditor\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Convert\ConvertArray;

/**
 * Class Mapper converts Address Service Data Object to an array
 */
class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     *
     * @param ProductInterface $product
     * @return array
     */
    public function toFlatArray(ProductInterface $product)
    {
        $flatArray = $this->extensibleDataObjectConverter->toNestedArray($product, [], '\Magento\Catalog\Api\Data\ProductInterface');
        return ConvertArray::toFlatArray($flatArray);
    }
}
