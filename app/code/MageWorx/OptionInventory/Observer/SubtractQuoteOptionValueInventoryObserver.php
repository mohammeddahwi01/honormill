<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \MageWorx\OptionInventory\Model\Validator;
use \MageWorx\OptionInventory\Model\StockProvider;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Magento\Quote\Model\Quote\Item as QuoteItem;
use \MageWorx\OptionInventory\Model\ResourceModel\StockManagement;

/**
 * Class SubtractQuoteOptionValueInventoryObserver.
 * This observer substruct requested qty from option values.
 *
 * @package MageWorx\OptionInventory\Observer
 */
class SubtractQuoteOptionValueInventoryObserver implements ObserverInterface
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var StockProvider
     */
    protected $stockProvider;

    /**
     * @var StockManagement
     */
    protected $stockManagement;

    /**
     * @var OptionValuesQty
     */
    protected $optionValuesQty;

    /**
     * SubtractQuoteOptionValueInventoryObserver constructor.
     *
     * @param StockManagement $stockManagement
     * @param OptionValuesQty $optionValuesQty
     */
    public function __construct(
        Validator $validator,
        StockProvider $stockProvider,
        StockManagement $stockManagement,
        OptionValuesQty $optionValuesQty
    ) {
        $this->validator = $validator;
        $this->stockProvider = $stockProvider;
        $this->stockManagement = $stockManagement;
        $this->optionValuesQty = $optionValuesQty;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $quoteItems = $quote->getAllItems();

        $requestedValues = $this->stockProvider->getRequestedData($quoteItems, []);
        $originQuoteValues = $this->stockProvider->getOriginData($requestedValues);

        $this->validator->validate($requestedValues, $originQuoteValues);

        $items = $this->optionValuesQty->getItemsToCorrect($requestedValues, $originQuoteValues);

        $this->stockManagement->correctItemsQty($items, '-');

        return $this;
    }
}
