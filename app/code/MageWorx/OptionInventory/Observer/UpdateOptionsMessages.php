<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class UpdateOptionsMessages.
 * This observer updates options stock message
 */
class UpdateOptionsMessages implements ObserverInterface
{
    /**
     * @var \MageWorx\OptionInventory\Model\StockProvider|null
     */
    protected $stockProvider = null;

    /**
     * UpdateOptionsMessages constructor.
     *
     * @param \MageWorx\OptionInventory\Model\StockProvider $stockProvider
     */
    public function __construct(
        \MageWorx\OptionInventory\Model\StockProvider $stockProvider
    ) {
        $this->stockProvider = $stockProvider;
    }

    /**
     * @param EventObserver $observer
     * @return mixed
     */
    public function execute(EventObserver $observer)
    {
        $configObj = $observer->getEvent()->getData('configObj');
        $options = $configObj->getData('config');

        $options = $this->stockProvider->updateOptionsStockMessage($options);

        $configObj->setData('config', $options);

        return $configObj;
    }
}