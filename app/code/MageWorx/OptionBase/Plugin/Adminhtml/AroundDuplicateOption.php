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

class AroundDuplicateOption
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

    public function aroundDuplicate($subject, \Closure $proceed, $object, $oldProductId, $newProductId)
    {
        $connection = $subject->getConnection();

        $optionsCond = [];
        $optionsData = [];

        $oldMageworxIds = [];
        $mapMageworxId = [];

        // read and prepare original product options
        $select = $connection->select()->from(
            $subject->getTable('catalog_product_option')
        )->where(
            'product_id = ?',
            $oldProductId
        );

        $query = $connection->query($select);

        while ($row = $query->fetch()) {
            $oldMageworxIds[$row['option_id']] = $row['mageworx_option_id'];

            $optionsData[$row['option_id']] = $row;
            $optionsData[$row['option_id']]['product_id'] = $newProductId;
            $optionsData[$row['option_id']]['mageworx_option_id'] = null;
            unset($optionsData[$row['option_id']]['option_id']);
        }

        // insert options to duplicated product
        foreach ($optionsData as $oId => $data) {
            $connection->insert($subject->getMainTable(), $data);
            $optionsCond[$oId] = $connection->lastInsertId($subject->getMainTable());
        }

        // copy options prefs
        foreach ($optionsCond as $oldOptionId => $newOptionId) {
            // title
            $table = $subject->getTable('catalog_product_option_title');

            $select = $subject->getConnection()->select()->from(
                $table,
                [new \Zend_Db_Expr($newOptionId), 'store_id', 'title']
            )->where(
                'option_id = ?',
                $oldOptionId
            );

            $insertSelect = $connection->insertFromSelect(
                $select,
                $table,
                ['option_id', 'store_id', 'title'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($insertSelect);

            // price
            $table = $subject->getTable('catalog_product_option_price');

            $select = $connection->select()->from(
                $table,
                [new \Zend_Db_Expr($newOptionId), 'store_id', 'price', 'price_type']
            )->where(
                'option_id = ?',
                $oldOptionId
            );

            $insertSelect = $connection->insertFromSelect(
                $select,
                $table,
                ['option_id', 'store_id', 'price', 'price_type'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($insertSelect);


            // mageworx option description
            $table = $subject->getTable('catalog_product_option');
            $select = $connection->select()->from(
                $table,
                ['mageworx_option_id']
            )->where(
                'option_id = ?',
                $newOptionId
            );

            $oldMageworxId = $oldMageworxIds[$oldOptionId];
            $newMageworxId = $connection->fetchOne($select);

            $table = $subject->getTable('mageworx_optionfeatures_option_description');
            $select = $connection->select()->from(
                $table,
                ['store_id', 'description']
            )->where(
                'mageworx_option_id = ?',
                $oldMageworxId
            );

            $descriptionData = $connection->fetchRow($select);
            $descriptionData['mageworx_option_id'] = $newMageworxId;

            $connection->insert($table, $descriptionData);

            $object->getValueInstance()->duplicate($oldOptionId, $newOptionId);

            $mapMageworxId[$oldMageworxId] = $newMageworxId; // used in the DuplicateDependency plugin
        }

        // save old => new mageworx_id to Magento Register
        $mapMageworxOptionId = $this->registry->registry('mapMageworxOptionId');
        if (!isset($mapMageworxOptionId)) {
            $this->registry->register('mapMageworxOptionId', $mapMageworxId);
        }

        return $object;
    }
}
