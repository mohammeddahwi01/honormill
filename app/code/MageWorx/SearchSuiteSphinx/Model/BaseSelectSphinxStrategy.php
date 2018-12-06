<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class BaseSelectSphinxStrategy
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function createBaseSelect()
    {
        $select = $this->resource->getConnection()->select();
        $mainTableAlias = 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        $select->joinInner(
            ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
            'search_index.attribute_id = cea.attribute_id',
            []
        );

        return $select;
    }
}
