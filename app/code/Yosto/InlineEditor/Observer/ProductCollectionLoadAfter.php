<?php
/**
 * Created by PhpStorm.
 * User: nghiata
 * Date: 31/07/2018
 * Time: 18:56
 */

namespace Yosto\InlineEditor\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;

class ProductCollectionLoadAfter implements ObserverInterface
{
    const IS_LOAD_CATEGORY_IDS = false;
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getCollection();
        if (!$collection->getData(self::IS_LOAD_CATEGORY_IDS)) {
            return;
        }
        return $this;
    }
}