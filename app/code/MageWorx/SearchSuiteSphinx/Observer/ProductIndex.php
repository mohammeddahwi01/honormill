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

class ProductIndex implements \Magento\Framework\Event\ObserverInterface
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
     * ProductIndex constructor.
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

        if (!($observer->getObject() instanceof \Magento\Catalog\Model\Product)) {
            return false;
        }

        if (!$this->productIndexer->isScheduled()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getObject();
            $origData = $product->getOrigData();
            $changedAttributes = [];

            foreach ($origData as $code => $value) {
                if ($value !== $product->getData($code)) {
                    $changedAttributes[] = $code;
                }
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
            $productAttributes = $this->attributeCollectionFactory->create();
            $productAttributes->addFieldToFilter('is_searchable', 1)
                ->addFieldToFilter('attribute_code', ['in' => $changedAttributes])
                ->addFieldToSelect('attribute_code')
                ->addFieldToSelect('frontend_input')
                ->addFieldToSelect('attribute_id');
            $dataToReplace = [];

            if ($product->getStoreId() == Store::DEFAULT_STORE_ID) {
                $productsStoreIds = $this->sphinxHelper->getAllStoreIdsByProducts([$product->getId()]);
                $storeIds = $productsStoreIds[$product->getId()];
            } else {
                $storeIds = [$product->getStoreId()];
            }

            foreach ($storeIds as $storeId) {
                foreach ($productAttributes as $attribute) {
                    $storeValue = $this->resourceProduct->getAttributeRawValue(
                        $product->getId(),
                        $attribute->getAttributeCode(),
                        $storeId
                    );

                    if ($storeValue && $product->getStoreId() == Store::DEFAULT_STORE_ID) {
                        continue;
                    }

                    $code = $attribute->getAttributeCode();
                    $data['entity_id']    = $product->getId();
                    $data['attribute_id'] = $attribute->getId();
                    if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                        $data['data_index'] = implode(', ', $product->getAttributeText($code));
                    } else {
                        $data['data_index'] = $product->getData($code);
                    }
                    $data['store_id']     = $storeId;
                    $dataToReplace[]      = $data;
                }
            }

            if (!empty($dataToReplace)) {
                $registryData[$product->getId()] = $dataToReplace;
                $this->registry->register('mageworx_sphinxsearch_product_update', $registryData, true);
                $this->productIndexer->executeRow($product->getId());
                $this->registry->register('mageworx_sphinxsearch_product_update', [], true);
            }
        }
        return true;
    }
}