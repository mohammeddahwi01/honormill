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
 * Class OrderCreditMemo. Refund option values qty when order is Credit Memo.
 */
class RefundQtyWhenOrderCreditMemo implements ObserverInterface
{
    /**
     * @var RefundQty
     */
    protected $refundQtyModel;

    /**
     * OrderCreditMemo constructor.
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
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        $items = $order->getAllItems();

        $this->refundQtyModel->refund($items, 'qty_refunded');

        return $this;
    }
}
