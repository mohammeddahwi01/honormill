<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Model\ResourceModel\Region;

use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;

class Collection
{
    /**
     * @param RegionCollection $collection
     * @param array $result
     *
     * @return array
     */
    public function afterToOptionArray(RegionCollection $collection, $result)
    {
        if (count($result) > 0) {
            array_shift($result);
            array_unshift(
                $result,
                ['title' => '', 'value' => '0', 'label' => __('Please select a region, state or province.')]
            );
        }

        return $result;
    }
}
