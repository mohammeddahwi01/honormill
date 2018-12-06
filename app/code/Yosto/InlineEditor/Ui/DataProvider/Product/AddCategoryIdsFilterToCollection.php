<?php
/**
 * Copyright © 2018 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\InlineEditor\Ui\DataProvider\Product;


use Magento\Framework\App\ResourceConnection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Framework\Data\Collection;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class AddCategoryIdsFilterToCollection implements AddFilterToCollectionInterface
{

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    function __construct(ResourceConnection $resourceConnection)
    {
        $this->_connection = $resourceConnection->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (empty($condition['like'])) {
            return;
        }

        $categoryIds =str_replace("%",'', $condition['like']);

        $tableName = $this->_connection->getTableName(
            "catalog_category_product"
        );
        $select = $this->_connection->select()
            ->from($tableName, ['product_id'])
            ->where("category_id in (" . $categoryIds . ")" );

        $data = $this->_connection->fetchAll($select);
        $productIds = array_column($data, 'product_id');

        $collection->getSelect()->where('e.entity_id IN (?)', $productIds);

    }
}