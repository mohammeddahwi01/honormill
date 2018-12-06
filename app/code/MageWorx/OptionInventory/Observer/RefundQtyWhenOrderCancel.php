<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \MageWorx\OptionInventory\Model\RefundQty;

/**
 * Class OrderCancel. Refund option values qty when order is Cancel.
 */
class RefundQtyWhenOrderCancel implements ObserverInterface
{
    /**
     * @var RefundQty
     */
    protected $refundQtyModel;

    /**
     * OrderCancel constructor.
     * @param RefundQty $refundQtyModel
     */
    public function __construct(
        RefundQty $refundQtyModel
    ) {
        $this->refundQtyModel = $refundQtyModel;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $items = $observer->getEvent()->getOrder()->getAllItems();

        $this->refundQtyModel->refund($items, 'qty_canceled');

        return $this;
    }
}
