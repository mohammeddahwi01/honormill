<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Observer;

use MageWorx\SearchSuiteSphinx\Model\Indexer\Product as ProductIndexer;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Store\Model\Store;
use MageWorx\SearchSuiteSphinx\Helper\Sphinx as SphinxHelper;
use MageWorx\SearchSuiteSphinx\Model\ResourceModel\Product as ResourceProduct;

class ProductAttributesIndex implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var SphinxHelper
     */
    protected $sphinxHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * ProductAttributesIndex constructor.
     *
     * @param ResourceProduct $resourceProduct
     * @param SphinxHelper $sphinxHelper
     * @param \Magento\Framework\Registry $registry
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param ProductIndexer $productIndexer
     */
    public function __construct(
        ResourceProduct $resourceProduct,
        SphinxHelper $sphinxHelper,
        \Magento\Framework\Registry $registry,
        AttributeCollectionFactory $attributeCollectionFactory,
        ProductIndexer $productIndexer
    ) {
        $this->resourceProduct            = $resourceProduct;
        $this->sphinxHelper               = $sphinxHelper;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productIndexer             = $productIndexer;
        $this->registry                   = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->productIndexer->isScheduled()) {
            $dataToReplace  = [];
            $attributesData = $observer->getAttributesData();
            $productsIds    = $observer->getProductIds();
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
            $productAttributes = $this->attributeCollectionFactory->create();
            $productAttributes->addFieldToFilter('is_searchable', 1)
                              ->addFieldToFilter('attribute_code', ['in' => array_keys($attributesData)])
                              ->addFieldToSelect('attribute_code')
                              ->addFieldToSelect('frontend_input')
                              ->addFieldToSelect('attribute_id');

            if ($observer->getStoreId() == Store::DEFAULT_STORE_ID) {
                $productsStoreIds = $this->sphinxHelper->getAllStoreIdsByProducts($productsIds);
            } else {
                $productsStoreIds = [];
                foreach ($productsIds as $productsId) {
                    $productsStoreIds[$productsId] = $observer->getStoreId();
                }
            }

            foreach ($productsStoreIds as $productId => $storeIds ) {
                foreach ($storeIds as $storeId) {

                    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                    foreach ($productAttributes as $attribute) {
                        $code = $attribute->getAttributeCode();
                        $storeValue = $this->resourceProduct->getAttributeRawValue(
                            $productId,
                            $code,
                            $storeId
                        );

                        if ($storeValue && $observer->getStoreId() == Store::DEFAULT_STORE_ID) {
                            continue;
                        }

                        $data['entity_id']           = $productId;
                        $data['attribute_id']        = $attribute->getId();

                        if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                            $data['data_index'] = implode(
                                ', ',
                                $attribute->getSource()->getOptionText($attributesData[$code])
                            );
                        } else {
                            $data['data_index'] = $attributesData[$code];
                        }
                        $data['store_id']            = $storeId;
                        $dataToReplace[$productId][] = $data;
                    }
                }
            }

            if (!empty($dataToReplace)) {
                $this->registry->register('mageworx_sphinxsearch_product_update', $dataToReplace, true);
                $this->productIndexer->executeList($productsIds);
                $this->registry->register('mageworx_sphinxsearch_product_update', [], true);
            }
        }

        return true;
    }
}