<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class DataSaver
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var \MageWorx\OptionBase\Helper\Data
     */
    protected $baseHelper;

    /**
     * @param ResourceConnection $resource
     * @param \MageWorx\OptionBase\Helper\Data $helperData
     */
    public function __construct(
        ResourceConnection $resource,
        \MageWorx\OptionBase\Helper\Data $helperData
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->baseHelper = $helperData;
    }

    /**
     * Delete option/option value data by certain condition
     *
     * @param string $tableName
     * @param string $condition
     * @return void
     */
    public function deleteData($tableName, $condition)
    {
        $this->connection->delete($this->resource->getTableName($tableName), $condition);
    }

    /**
     * Insert multiple option/option value data
     *
     * @param string $tableName
     * @param array $data
     * @return void
     */
    public function insertMultipleData($tableName, $data)
    {
        $this->connection->insertMultiple($this->resource->getTableName($tableName), $data);
    }

    /**
     * Update table catalog_product_entity
     *
     * @param $productId
     * @param $isRequire
     */
    public function updateValueIsRequire($productId, $isRequire)
    {
        $columnIdName = $this->baseHelper->isEnterprise() ? 'row_id' : 'entity_id';
        $where        = [$columnIdName . '=' . $productId];
        $this->connection->update(
            $this->resource->getTableName('catalog_product_entity'),
            ['mageworx_is_require' => $isRequire],
            $where
        );
    }
}
