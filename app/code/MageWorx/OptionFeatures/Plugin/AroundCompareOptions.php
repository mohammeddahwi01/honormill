<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Plugin;

use Magento\Quote\Model\Quote\Item;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class AroundCompareOptions
{
    /**
     * @var Helper
     */
    protected $helper;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param Item $subject
     * @param \Closure $proceed
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function aroundCompareOptions(Item $subject, \Closure $proceed, $options1, $options2)
    {
        if (!$this->helper->isQtyInputEnabled()) {
            return $proceed($options1, $options2);
        }

        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }
}
