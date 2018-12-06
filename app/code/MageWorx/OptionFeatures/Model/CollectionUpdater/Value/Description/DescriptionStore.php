<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\CollectionUpdater\Value\Description;

use MageWorx\OptionBase\Model\CollectionUpdate\AbstractUpdater;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;

class DescriptionStore extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions($conditions)
    {
        return [$this->getTableAlias() => $this->getTableName($conditions['entity_type'])];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName(OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(OptionTypeDescription::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        $storeId = $this->systemHelper->resolveCurrentStoreId();
        $conditions = 'main_table.mageworx_option_type_id = ' . $this->getTableAlias() . '.mageworx_option_type_id';
        $conditions .= " AND " . $this->getTableAlias() . ".store_id = '" . $storeId . "'";
        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        $descriptionExpr = $this->resource->getConnection()->getCheckSql(
            'store_option_type_description.description IS NULL',
            'default_option_type_description.description',
            'store_option_type_description.description'
        );
        return [
            'store_description' => $this->getTableAlias() . '.description',
            'description' => $descriptionExpr
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'store_option_type_description';
    }
}
