<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Feed\Grid;

class ExecuteModeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            'manual' => __('Manually'),
            'schedule' => __('By Schedule'),
        ];
    }
}
