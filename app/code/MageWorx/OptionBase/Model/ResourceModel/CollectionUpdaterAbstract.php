<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\ResourceModel;

use \Magento\Catalog\Model\ResourceModel\Product\Option\Collection as OptionCollection;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ValueCollection;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionBase\Model\CollectionUpdate\Option\Updaters as OptionCollectionUpdaters;
use MageWorx\OptionBase\Model\CollectionUpdate\Option\Value\Updaters as ValueCollectionUpdaters;

abstract class CollectionUpdaterAbstract
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var OptionCollection|ValueCollection
     */
    protected $collection;

    /**
     * @var CollectionUpdaterRegistry
     */
    protected $collectionUpdaterRegistry;

    /**
     * @var OptionCollectionUpdaters
     */
    protected $optionCollectionUpdaters;

    /**
     * @var ValueCollectionUpdaters
     */
    protected $valueCollectionUpdaters;

    /**
     * @var array
     */
    protected $conditions;

    /**
     * @param $collection AbstractCollection
     * @param ResourceConnection $resource
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param OptionCollectionUpdaters $optionCollectionUpdaters
     * @param ValueCollectionUpdaters $valueCollectionUpdaters
     * @param array $conditions
     */
    public function __construct(
        AbstractCollection $collection,
        ResourceConnection $resource,
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        OptionCollectionUpdaters $optionCollectionUpdaters,
        ValueCollectionUpdaters $valueCollectionUpdaters,
        $conditions = []
    ) {
        $this->collection = $collection;
        $this->resource = $resource;
        $this->collectionUpdaterRegistry = $collectionUpdaterRegistry;
        $this->optionCollectionUpdaters = $optionCollectionUpdaters;
        $this->valueCollectionUpdaters = $valueCollectionUpdaters;
        $this->conditions = $conditions;
    }

    /**
     * Add updaters to collection
     * @return string
     */
    final public function update()
    {
        $entityId = $this->collectionUpdaterRegistry->getCurrentEntityId() ? : 0;
        $entityType = $this->collectionUpdaterRegistry->getCurrentEntityType() ? : 'product';

        $optionValueIds = $this->collectionUpdaterRegistry->getOptionValueIds();
        $optionIds = $this->collectionUpdaterRegistry->getOptionIds();

        $this->conditions['value_id'] = $optionValueIds ? $optionValueIds : [];
        $this->conditions['option_id'] = $optionIds ? $optionIds : [];
        $this->conditions['entity_id'] = $entityId;
        $this->conditions['entity_type'] = $entityType;
        $this->conditions['row_id'] = $this->collectionUpdaterRegistry->getCurrentRowId();

        $this->process();
    }
}
