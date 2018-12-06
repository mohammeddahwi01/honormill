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
class ProductDeltaIndex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_eventPrefix = 'mageworx_sphinxsearch_product_delta_collection';
    protected $_eventObject = 'product_delta_collection';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrlHelper;

    protected $tableName = 'mageworx_catalogsearch_product_delta';

    /**
     * ProductDeltaIndex constructor.
     *
     * @param \Magento\Backend\Model\UrlInterface $backendUrlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Helper $helper
     * @param \Magento\Framework\Registry $registry
     * @param ProductResource $productResource
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Helper $helper,
        \Magento\Framework\Registry $registry,
        ProductResource $productResource,
        Context $context,
        $connectionName = null
    ) {
        $this->backendUrlHelper = $backendUrlHelper;
        $this->messageManager   = $messageManager;
        $this->helper           = $helper;
        $this->registry         = $registry;
        $this->productResource  = $productResource;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->tableName, 'entity_id');
    }

    /**
     * @param array $ids
     */
    public function saveNewData($ids)
    {
        $dataToInsert = [];
        $registryData = $this->registry->registry('mageworx_sphinxsearch_product_update');
        $where = '';
        foreach ($ids as $id) {
            if (empty($registryData[$id])) {
                continue;
            }
            $dataToInsert = array_merge($dataToInsert, $registryData[$id]);
            foreach ($registryData[$id] as $row) {
                if ($where) {
                    $where .= ' OR ';
                }
                $where .= '(entity_id = "' . (int)$row['entity_id']
                    . '" AND attribute_id = "' . (int)$row['attribute_id']
                    . '" AND store_id = "' . (int)$row['store_id'] . '")';
            }
        }

        if ($where) {
            $this->getConnection()->delete($this->getTable($this->tableName), $where);
        }

        if (!empty($dataToInsert)) {
            $this->getConnection()->insertMultiple(
                $this->getTable($this->tableName),
                $dataToInsert
            );
        }

        if ($this->getProductsCount() > $this->helper->getMaxProductCountInDeltaIndex()) {
            $msg = __('Sphinx Delta Index is too big. Products can be saved slowly.
            Click "Reindex All" button in Sphinx Search settings to resolve this issue.');
            $this->messageManager->addWarningMessage($msg);
        }
    }

    /**
     * Delete delta index while full reindex
     */
    public function emptyProductDelta()
    {
        $this->getConnection()->delete($this->getTable($this->tableName));
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTable($this->tableName), 'entity_id')->group('entity_id');

        return count($this->getConnection()->fetchAll($select));
    }
}