<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model\ResourceModel;

use \Magento\Catalog\Model\ResourceModel\Product\Option\Collection as OptionCollection;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ValueCollection;

abstract class CollectionUpdaterAbstract
{
    /**
     * @var OptionCollection|ValueCollection
     */
    protected $collection;

    /**
     * @param $collection OptionCollection|ValueCollection
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * Add dependencies to collection
     * @return string
     */
    final public function update()
    {
        $alias = $this->getDependencyTableAlias();
        $onClauseField = $this->onClauseField();
        $table = $this->getDependencyTable();

        $partFrom = $this->collection->getSelect()->getPart('from');
        if (array_key_exists($alias, $partFrom)) {
            return $this->collection;
        }

        $this->collection
            ->getResource()
            ->getConnection()
            ->query('SET SESSION group_concat_max_len = 100000;');

        $this->collection->getSelect()->joinLeft(
            [$alias => $table],
            $alias.'.child_'.$onClauseField.' = main_table.mageworx_'.$onClauseField,
            ['field_hidden_dependency' => $alias.'.dependency']
        );
    }

    /**
     * Get dependency table alias
     * @return string
     */
    public function getDependencyTableAlias()
    {
        return '';
    }

    /**
     * On clause field
     * @return string
     */
    public function onClauseField()
    {
        return '';
    }

    /**
     * Assembled expression to get dependency table
     * @return string
     */
    public function getDependencyTable()
    {
        return '';
    }
}
