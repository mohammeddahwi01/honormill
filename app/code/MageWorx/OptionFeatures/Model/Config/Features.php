<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model\Config;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractExtensibleModel;

class Features extends AbstractExtensibleModel
{
    /**
     * Get is default config array
     *
     * @param Product $product
     * @return array
     */
    public function getIsDefaultArray($product)
    {
        $result = [];
        if (!empty($product->getOptions())) {
            foreach ($product->getOptions() as $option) {
                if (!empty($option->getValues())) {
                    foreach ($option->getValues() as $value) {
                        if ($value->getIsDefault() != 1) {
                            continue;
                        }
                        $result[$value->getMageworxOptionTypeId()] = $option->getType();
                    }
                }
            }
        }

        return $result;
    }
}
