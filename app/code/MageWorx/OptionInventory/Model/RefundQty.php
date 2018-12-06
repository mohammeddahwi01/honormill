<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model;

use \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as ValueCollection;

/**
 * Class RefundQty. Refund option values qty when order is cancel or credit memo.
 */
class RefundQty
{
    /**
     * @var ValueCollection
     */
    protected $valueCollection;

    /**
     * RefundQty constructor.
     * @param ValueCollection $valueCollection
     */
    public function __construct(
        ValueCollection $valueCollection
    ) {
        $this->valueCollection = $valueCollection;
    }

    /**
     * Refund qty when order is cancele or credit memo.
     * Walk through the all order $items, find count qty to refund by the $qtyFieldName
     * and refund it for all option values in this order.
     *
     * @param array $items
     * @param string $qtyFieldName
     * @return $this
     */
    public function refund($items, $qtyFieldName)
    {
        foreach ($items as $item) {
            $itemData = $item->getData();
            $itemProductInfo = $itemData['product_options']['info_buyRequest'];

            if (!isset($itemProductInfo['options'])) {
                continue;
            }

            $qtyCanceled = $itemData[$qtyFieldName];
            $itemOptions = $itemProductInfo['options'];

            $valueIds = [];
            foreach ($itemOptions as $optionId => $value) {
                if (is_array($value)) {
                    foreach ($value as $valueId) {
                        $valueIds[] = $valueId;
                    }
                } else {
                    $valueIds[] = $value;
                }
            }

            $valuesCollection = $this->valueCollection
                ->create()
                ->getValuesByOption($valueIds)
                ->load();

            if (!$valuesCollection->getSize()) {
                continue;
            }

            foreach ($valueIds as $valueId) {
                $valueModel = $valuesCollection->getItemById($valueId);

                if (!$valueModel) {
                    continue;
                }

                if ($valueModel->getSku() || !$valueModel->getManageStock()) {
                    continue;
                }

                $valueModel->setQty($valueModel->getQty() + $qtyCanceled);
            }

            $valuesCollection->save();
        }

        return $this;
    }
}
