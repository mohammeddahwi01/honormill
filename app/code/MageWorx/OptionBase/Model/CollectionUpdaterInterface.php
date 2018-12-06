<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

interface CollectionUpdaterInterface
{
    /**
     * Get from conditions for sql join
     * @param string $conditions
     * @return array
     */
    public function getFromConditions($conditions);

    /**
     * Get table name for sql join
     *
     * @param string $entityType
     * @return string
     */
    public function getTableName($entityType);

    /**
     * Get sql join's "ON" condition clause
     * Example:
     * @return string
     */
    public function getOnConditionsAsString();

    /**
     * Get columns for sql join
     * @return array
     */
    public function getColumns();

    /**
     * Get table alias for sql join
     * @return string
     */
    public function getTableAlias();
}
