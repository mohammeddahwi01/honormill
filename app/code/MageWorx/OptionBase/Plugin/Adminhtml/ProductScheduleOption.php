<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Adminhtml;

use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use \Magento\Framework\App\Request\Http as HttpRequest;

class ProductScheduleOption
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var HttpRequest
     */
    protected $request;

    public function __construct(
        OptionBaseHelper $helper,
        HttpRequest $request
    ) {
    
        $this->helper = $helper;
        $this->request = $request;
    }

    public function beforeSave($repository, $option)
    {
        if ($this->out()) {
            return [$option];
        }

        $option->setMageworxOptionId(null);

        return [$option];
    }

    private function out()
    {
        if (!$this->request->getParam('staging')) {
            return true;
        }

        if (!$this->helper->isEnterprise()) {
            return true;
        }

        return false;
    }
}
