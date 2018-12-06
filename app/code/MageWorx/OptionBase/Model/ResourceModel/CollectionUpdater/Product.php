<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Model\CollectionUpdate\Product\Updaters as ProductCollectionUpdaters;
use Magento\Catalog\Api\Data\ProductInterface;

class Product
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductCollection
     */
    protected $collection;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var ProductCollectionUpdaters
     */
    protected $productCollectionUpdaters;

    /**
     * @param ResourceConnection $resource
     * @param ProductCollection $collection
     * @param BaseHelper $baseHelper
     * @param ProductCollectionUpdaters $productCollectionUpdaters
     */
    public function __construct(
        ResourceConnection $resource,
        ProductCollection $collection,
        BaseHelper $baseHelper,
        ProductCollectionUpdaters $productCollectionUpdaters
    ) {
        $this->resource = $resource;
        $this->collection = $collection;
        $this->baseHelper = $baseHelper;
        $this->productCollectionUpdaters = $productCollectionUpdaters;
    }

    /**
     * Add updaters to collection
     * @return string
     */
    public function update()
    {
        $alias = '';
        $productTableName = '';
        $templateTableName = '';
        $attributeKeys = [];
        $partFrom = $this->collection->getSelect()->getPart('from');

        foreach ($this->productCollectionUpdaters->getData() as $productCollectionUpdater) {
            $alias = $productCollectionUpdater->getTableAlias();
            $productTableName = $productCollectionUpdater->getProductTableName();
            $templateTableName = $productCollectionUpdater->getTemplateTableName();
            $attributeKeys = array_merge($attributeKeys, $productCollectionUpdater->getColumns());
        }

        if (array_key_exists($alias, $partFrom)
            || empty($productTableName)
            || empty($templateTableName)
            || empty($attributeKeys)
            || empty($alias)
        ) {
            return $this->collection;
        }

        if ($partFrom[ProductCollection::MAIN_TABLE_ALIAS]['tableName'] ==
            $this->resource->getTableName($templateTableName)) {
            $tableName = $this->resource->getTableName($templateTableName);
            $condition = '`' . ProductCollection::MAIN_TABLE_ALIAS . '`.`group_id` = `' . $alias . '`.`group_id`';
        } else {
            $tableName = $this->resource->getTableName($productTableName);
            $condition = '`' . ProductCollection::MAIN_TABLE_ALIAS . '`.`' .
                $this->baseHelper->getLinkField(ProductInterface::class) . '` = `' . $alias . '`.`product_id`';
        }

        $this->collection->getSelect()->joinLeft(
            [$alias => $tableName],
            $condition,
            $attributeKeys
        );

        return $this->collection;
    }
}
