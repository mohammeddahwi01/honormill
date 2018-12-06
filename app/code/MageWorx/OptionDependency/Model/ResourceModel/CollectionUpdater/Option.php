<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model\ResourceModel\CollectionUpdater;

use \MageWorx\OptionDependency\Model\ResourceModel\CollectionUpdaterAbstract;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Collection;

class Option extends CollectionUpdaterAbstract
{
    /**
     * @var string
     */
    protected $mainTableName;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $collection
     * @param string $mainTableName
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $collection,
        $mainTableName
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->mainTableName = $mainTableName;
        parent::__construct($collection);
    }

    /**
     * Get dependency table alias
     * @return string
     */
    public function getDependencyTableAlias()
    {
        return $this->connection->getTableName('mageworx_option_dependency');
    }

    /**
     * On clause field
     * @return string
     */
    public function onClauseField()
    {
        return 'option_id';
    }

    /**
     * Assembled expression to get dependency table
     * @return string
     */
    public function getDependencyTable()
    {
        $statement = $this->connection->select()
            ->from(
                $this->resource->getTableName($this->mainTableName),
                [
                    'child_option_id',
                    'dependency' => 'concat(
                        \'[\',
                        group_concat(concat(\'["\', parent_option_id, \'","\', parent_option_type_id, \'"]\')),
                        \']\'
                    )',
                ]
            )
            ->where('child_option_type_id = ?', '')
            ->group('child_option_id');

        return new \Zend_Db_Expr('('.$statement->assemble().')');
    }
}
