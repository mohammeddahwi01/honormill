<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Model\ProductAttributeInterface;
use MageWorx\OptionBase\Model\Product\AbstractProductAttribute;
use MageWorx\OptionFeatures\Model\ProductAttributes;
use MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\CollectionFactory as AttributesCollectionFactory;

class Absolutes extends AbstractProductAttribute implements ProductAttributeInterface
{
    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @var AttributesCollectionFactory
     */
    protected $attributesCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param AttributesCollectionFactory $attributesCollectionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        BaseHelper $baseHelper,
        AttributesCollectionFactory $attributesCollectionFactory
    ) {
        parent::__construct($resource);
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->attributesCollectionFactory = $attributesCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        $attributeKeys = [
            Helper::KEY_ABSOLUTE_PRICE,
            Helper::KEY_ABSOLUTE_COST,
            Helper::KEY_ABSOLUTE_WEIGHT,
            'is_stocktab',
            'is_customtab'
        ];
        foreach ($this->helper->getAdditionalProductAttributes() as $additionalAttributeKey) {
            $attributeKeys[] = $additionalAttributeKey;
        }
        return $attributeKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        $map = [
            'product' => ProductAttributes::TABLE_NAME,
            'group' => ProductAttributes::OPTIONTEMPLATES_TABLE_NAME
        ];
        return $map[$this->entity->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function applyData($entity)
    {
        $this->entity = $entity;

        $data = [];
        foreach ($this->getKeys() as $attributeKey) {
            if ($this->entity->getDataObject()->getData($attributeKey)) {
                $data[$attributeKey] = $this->entity->getDataObject()->getData($attributeKey);
            }
        }

        if ($data) {
            $this->saveAbsolutes($data);
        }

        return;
    }

    /**
     * Save absolutes to database
     *
     * @param array $data
     */
    public function saveAbsolutes($data)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($this->getTableName($this->entity->getType()));
        if ($this->entity->getType() == 'product') {
            $linkField = $this->entity->getDataObject()->getResource()->getLinkField();
            $connection->delete(
                $tableName,
                [
                    'product_id = ?' => $this->entity->getDataObject()->getData($linkField)
                ]
            );
            $connection->insert($tableName, array_merge($data, ['product_id' => $this->entity->getDataObject()->getData($linkField)]));
        } elseif ($this->entity->getType() == 'group') {
            $linkField = $this->entity->getDataObjectIdName();
            $connection->update(
                $tableName,
                $data,
                $linkField . " = '" . $this->entity->getDataObject()->getData($linkField) . "'"
            );
        }
    }

    /**
     * Get item by product ID
     *
     * @param Product $product
     * @return ProductAttributes|null
     */
    public function getItemByProduct($product)
    {
        /** @var \MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\Collection $attributesCollection */
        $attributesCollection = $this->attributesCollectionFactory->create();
        /** @var \MageWorx\OptionFeatures\Model\ProductAttributes $item */
        $id = $this->baseHelper->isEnterprise() ?
            $product->getRowId() :
            $product->getId();
        $item = $attributesCollection->getItemByColumnValue('product_id', $id);
        return $item;
    }
}
