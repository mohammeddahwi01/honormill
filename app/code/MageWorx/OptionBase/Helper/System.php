<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;

class System extends AbstractHelper
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param State $state
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        State $state,
        StoreManagerInterface $storeManager
    ) {
        $this->state = $state;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return int
     */
    public function resolveCurrentStoreId()
    {
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            // in admin area
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->_request;
            $storeId = (int) $request->getParam('store', 0);
        } else {
            // frontend area
            $storeId = true; // get current store from the store resolver
        }

        $store = $this->storeManager->getStore($storeId);

        return $store->getId();
    }
}
