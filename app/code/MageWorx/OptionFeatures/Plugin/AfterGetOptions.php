<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin;

use MageWorx\OptionFeatures\Helper\Data as Helper;

class AfterGetOptions
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     *
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetOptions($subject, $result)
    {
        $selectOptionTypes = $this->helper->getSelectableOptionTypes();
        if ($this->helper->isEnabledIsDisabled()) {
            foreach ($result as $key => $item) {
                if (!in_array($item->getType(), $selectOptionTypes)) {
                    return $result;
                }
                if (!$item->getValues()) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

}