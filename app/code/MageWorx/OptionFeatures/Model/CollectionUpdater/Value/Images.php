<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\CollectionUpdater\Value;

use MageWorx\OptionBase\Model\CollectionUpdate\AbstractUpdater;
use MageWorx\OptionFeatures\Model\Image;

class Images extends AbstractUpdater
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
            return $this->resource->getTableName(Image::OPTIONTEMPLATES_TABLE_NAME);
        }
        return $this->resource->getTableName(Image::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        return 'main_table.mageworx_option_type_id = ' . $this->getTableAlias() . '.mageworx_option_type_id';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return ['images_data' => $this->getTableAlias() . '.images_data'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return $this->resource->getConnection()->getTableName('option_value_images');
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

        $selectImagesExpr = "SELECT mageworx_option_type_id,";
        $selectImagesExpr .= " concat('[',";
        $selectImagesExpr .= " group_concat(concat(";
        $selectImagesExpr .= "'{\"value\"',':\"',IFNULL(value,''),'\",',";
        $selectImagesExpr .= "'\"option_type_image_id\"',':\"',option_type_image_id,'\",',";
        $selectImagesExpr .= "'\"title_text\"',':\"',IFNULL(title_text,''),'\",',";
        $selectImagesExpr .= "'\"sort_order\"',':\"',sort_order,'\",',";
        $selectImagesExpr .= "'\"base_image\"',':\"',base_image,'\",',";
        $selectImagesExpr .= "'\"replace_main_gallery_image\"',':\"',IFNULL(replace_main_gallery_image,''),'\",',";
        $selectImagesExpr .= "'\"custom_media_type\"',':\"',media_type,'\",',";
        $selectImagesExpr .= "'\"color\"',':\"',IFNULL(color,''),'\",',";
        $selectImagesExpr .= "'\"disabled\"',':\"',disabled,'\",',";
        $selectImagesExpr .= "'\"tooltip_image\"',':\"',tooltip_image,'\"}'";
        $selectImagesExpr .= ")),";
        $selectImagesExpr .= "']')";
        $selectImagesExpr .= " AS images_data FROM " . $tableName;

        if ($conditions && (!empty($conditions['option_id']) || !empty($conditions['value_id']))) {
            $mageworxOptionTypeIds = $this->helper->findMageWorxOptionTypeIdByConditions($conditions);

            if (is_array($mageworxOptionTypeIds) && count($mageworxOptionTypeIds) > 0) {
                $selectImagesExpr .= " WHERE mageworx_option_type_id IN(" . implode(',', $mageworxOptionTypeIds) . ")";
            }
        }
        $selectImagesExpr .= " GROUP BY mageworx_option_type_id";

        return new \Zend_Db_Expr('(' . $selectImagesExpr . ')');
    }
}
