<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use MageWorx\SearchSuiteSphinx\Helper\Data as Helper;

/**
 * {@inheritdoc}
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    /**
     * Return real store value(NULL in case value set up only for default store)
     *
     * @param int $entityId
     * @param array|int|string $attr
     * @param int|\Magento\Store\Model\Store $storeId
     * @return array|bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeRawValue($entityId, $attr, $storeId)
    {
        if (!$entityId || empty($attr)) {
            return false;
        }
        if (!is_array($attr)) {
            $attr = [$attr];
        }

        $attrsData        = [];
        $staticAttributes = [];
        $typedAttributes  = [];
        $staticTable      = null;
        $connection       = $this->getConnection();

        foreach ($attr as $item) {
            /* @var $item \Magento\Catalog\Model\Entity\Attribute */
            $item = $this->getAttribute($item);
            if (!$item) {
                continue;
            }
            $attrCode  = $item->getAttributeCode();
            $attrTable = $item->getBackend()->getTable();
            $isStatic  = $item->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attrCode;
                $staticTable        = $attrTable;
            } else {
                $typedAttributes[$attrTable][$item->getId()] = $attrCode;
            }
        }

        if ($staticAttributes) {
            $select    = $connection->select()->from(
                $staticTable,
                $staticAttributes
            )->join(
                ['e' => $this->getTable($this->getEntityTable())],
                'e.' . $this->getLinkField() . ' = ' . $staticTable . '.' . $this->getLinkField()
            )->where(
                'e.entity_id = :entity_id'
            );
            $attrsData = $connection->fetchRow($select, ['entity_id' => $entityId]);
        }

        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }

        $storeId = (int)$storeId;
        if ($typedAttributes) {
            foreach ($typedAttributes as $table => $_attributes) {
                $select = $connection->select()
                                     ->from(['def_value' => $table], ['attribute_id'])
                                     ->join(
                                         ['e' => $this->getTable($this->getEntityTable())],
                                         'e.' . $this->getLinkField() . ' = ' . 'def_value.' . $this->getLinkField(),
                                         ''
                                     )->where('def_value.attribute_id IN (?)', array_keys($_attributes))
                                     ->where("e.entity_id = :entity_id")
                                     ->where('def_value.store_id = ?', 0);

                $bind = ['entity_id' => $entityId];

                if ($storeId != $this->getDefaultStoreId()) {
                    $joinCondition = [
                        $connection->quoteInto('value_store.attribute_id IN (?)', array_keys($_attributes)),
                        "value_store.{$this->getLinkField()} = e.{$this->getLinkField()}",
                        'value_store.store_id = :store_id',
                    ];

                    $select->joinLeft(
                        ['value_store' => $table],
                        implode(' AND ', $joinCondition),
                        ['attr_value' => 'value_store.value']
                    );

                    $bind['store_id'] = $storeId;
                } else {
                    $select->columns(['attr_value' => 'value'], 'def_value');
                }

                $result = $connection->fetchPairs($select, $bind);
                foreach ($result as $attrId => $value) {
                    $attrCode             = $typedAttributes[$table][$attrId];
                    $attrsData[$attrCode] = $value;
                }
            }
        }

        if (is_array($attrsData) && sizeof($attrsData) == 1) {
            $_data     = each($attrsData);
            $attrsData = $_data[1];
        }

        return $attrsData;
    }
}