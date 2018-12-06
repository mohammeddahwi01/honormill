<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin;

use \MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterFactory;

class ExtendOptionValueCollection
{
    protected $collectionUpdaterFactory;

    /**
     * BeforeLoad constructor.
     *
     * @param CollectionUpdaterFactory $collectionUpdaterFactory
     */
    public function __construct(
        CollectionUpdaterFactory $collectionUpdaterFactory
    ) {
        $this->collectionUpdaterFactory = $collectionUpdaterFactory;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad($collection, $printQuery = false, $logQuery = false)
    {
        $this->collectionUpdaterFactory->create($collection)->update();

        return [$printQuery, $logQuery];
    }
}
