<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Adminhtml;

use MageWorx\OptionBase\Model\Entity\Base as BaseEntityModel;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use \Magento\Framework\App\Request\Http as HttpRequest;
use \Magento\Framework\Registry;

class AroundDuplicateOptionValue
{
    /**
     * @var BaseEntityModel
     */
    protected $baseEntityModel;

    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        BaseEntityModel $baseEntityModel,
        OptionBaseHelper $helper,
        HttpRequest $request,
        Registry $registry
    ) {
    
        $this->baseEntityModel = $baseEntityModel;
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    public function aroundDuplicate($subject, \Closure $proceed, $object, $oldOptionId, $newOptionId)
    {
        $connection = $subject->getConnection();
        $select = $connection->select()->from($subject->getMainTable())->where('option_id = ?', $oldOptionId);
        $valueData = $connection->fetchAll($select);

        $valueCond = [];
        $oldMageworxIds = [];
        $mapMageworxId = [];

        foreach ($valueData as $data) {
            $oldMageworxIds[$data['option_type_id']] = $data['mageworx_option_type_id'];
            $optionTypeId = $data[$subject->getIdFieldName()];
            unset($data[$subject->getIdFieldName()]);
            $data['option_id'] = $newOptionId;
            $data['mageworx_option_type_id'] = null;

            $connection->insert($subject->getMainTable(), $data);
            $valueCond[$optionTypeId] = $connection->lastInsertId($subject->getMainTable());
        }

        unset($valueData);

        foreach ($valueCond as $oldTypeId => $newTypeId) {
            // price
            $priceTable = $subject->getTable('catalog_product_option_type_price');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'price', 'price_type'];

            $select = $connection->select()->from(
                $priceTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $connection->insertFromSelect(
                $select,
                $priceTable,
                ['option_type_id', 'store_id', 'price', 'price_type']
            );
            $connection->query($insertSelect);

            // title
            $titleTable = $subject->getTable('catalog_product_option_type_title');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'title'];

            $select = $subject->getConnection()->select()->from(
                $titleTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $connection->insertFromSelect(
                $select,
                $titleTable,
                ['option_type_id', 'store_id', 'title']
            );
            $connection->query($insertSelect);

            // mageworx option value:
            $table = $subject->getTable('catalog_product_option_type_value');
            $select = $connection->select()->from(
                $table,
                ['mageworx_option_type_id']
            )->where(
                'option_type_id = ?',
                $newTypeId
            );

            $oldMageworxId = $oldMageworxIds[$oldTypeId];
            $newMageworxId = $connection->fetchOne($select);

            // copy description
            $table = $subject->getTable('mageworx_optionfeatures_option_type_description');
            $select = $connection->select()->from(
                $table,
                ['store_id', 'description']
            )->where(
                'mageworx_option_type_id = ?',
                $oldMageworxId
            );

            $descriptionData = $connection->fetchRow($select);
            $descriptionData['mageworx_option_type_id'] = $newMageworxId;

            $connection->insert($table, $descriptionData);

            // copy images
            $table = $subject->getTable('mageworx_optionfeatures_option_type_image');
            $select = $connection->select()->from(
                $table,
                [
                    'media_type',
                    'value',
                    'title_text',
                    'sort_order',
                    'base_image',
                    'tooltip_image',
                    'color',
                    'replace_main_gallery_image',
                    'disabled'
                ]
            )->where(
                'mageworx_option_type_id = ?',
                $oldMageworxId
            );

            $imagesData = $connection->fetchAll($select);
            if (count($imagesData)) {
                foreach ($imagesData as $index => $data) {
                    $imagesData[$index]['mageworx_option_type_id'] = $newMageworxId;
                }

                $connection->insertMultiple($table, $imagesData);
            }

            $mapMageworxId[$oldMageworxId] = $newMageworxId; // used in the DuplicateDependency plugin
        }

        // save old => new mageworx_id to Magento Register
        $mapMageworxOptionTypeId = $this->registry->registry('mapMageworxOptionTypeId');
        if (isset($mapMageworxOptionTypeId)) {
            $this->registry->unregister('mapMageworxOptionTypeId');
            $mapMageworxId += $mapMageworxOptionTypeId;
        }
        $this->registry->register('mapMageworxOptionTypeId', $mapMageworxId);

        return $object;
    }
}
