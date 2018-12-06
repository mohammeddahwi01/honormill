<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\CollectionUpdater\Option;

use MageWorx\OptionBase\Model\CollectionUpdate\AbstractUpdater;
use MageWorx\OptionDependency\Model\Config;

class Dependency extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions($conditions)
    {
        $alias = $this->getTableAlias();
        $table = $this->getTable($conditions);
        return [$alias => $table];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(Config::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(Config::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        return $this->getTableAlias().'.child_option_id = main_table.mageworx_option_id';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return ['field_hidden_dependency' => $this->getTableAlias().'.dependency'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'mageworx_option_dependency';
    }

    /**
     * Get table for from conditions
     *
     * @param array $conditions
     * @return \Zend_Db_Expr
     */
    private function getTable($conditions)
    {
        $entityType = $conditions['entity_type'];
        $tableName = $this->getTableName($entityType);

        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 100000;');

        $statement = $this->resource->getConnection()->select()
            ->from(
                $tableName,
                [
                    'child_option_id',
                    'dependency' => 'concat(
                            \'[\',
                            group_concat(concat(\'["\', parent_option_id, \'","\', parent_option_type_id, \'"]\')),
                            \']\'
                        )',
                ]
            );

        if (!empty($conditions['entity_id'])) {
            if ($entityType == 'group') {
                $statement->where("child_option_type_id = '' AND group_id = ?", $conditions['entity_id']);
            } else {
                $statement->where(
                    "child_option_type_id = '' AND product_id = ?",
                    $conditions['row_id'] ? $conditions['row_id'] : $conditions['entity_id']
                );
            }
        } else {
            $statement->where('child_option_type_id = ?', '');
        }

        $statement->group('child_option_id');

        return new \Zend_Db_Expr('('.$statement->assemble().')');
    }
}
