<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\ManagerInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Call mageworx_attributes_save_trigger event to save attributes on product save
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $this->eventManager->dispatch(
            'mageworx_attributes_save_trigger',
            ['product' => $product, 'after_template' => false]
        );
    }
}
