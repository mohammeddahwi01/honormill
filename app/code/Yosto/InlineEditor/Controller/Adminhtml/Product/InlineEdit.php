<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\InlineEditor\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var ProductInterface */
    private $product;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var \Magento\Framework\Controller\Result\JsonFactory  */
    protected $resultJsonFactory;

    /** @var \Magento\Framework\Api\DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var \Yosto\InlineEditor\Model\Product\Mapper  */
    protected $productMapper;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected static $_attributeBackendTables = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * InlineEdit constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Yosto\InlineEditor\Model\Product\Mapper $productMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Yosto\InlineEditor\Model\ProductRepository $productRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Yosto\InlineEditor\Model\Product\Mapper $productMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Yosto\InlineEditor\Model\ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productMapper = $productMapper;
        $this->productRepository= $productRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $error = false;

        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $this->updateProductAttributes($postItems, $storeId, $messages);
        $this->assignCategories($postItems, $messages);
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $postItems
     * @param $storeId
     * @param $error
     */
    public function updateProductAttributes($postItems, $storeId, $error) {

        $eavConfig = $this->_objectManager->create(\Magento\Eav\Model\Config::class);

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class);

        /** @var AdapterInterface $connection */
        $connection = $resource->getConnection();

        $excludedFields = ['entity_id','type_id', 'attribute_set_id', 'qty', 'category_ids', 'sku'];

        /** @var \Magento\CatalogInventory\Model\StockRegistry  $stockRegistry */
        $stockRegistry = $this->_objectManager->create(\Magento\CatalogInventory\Model\StockRegistry::class);

        /** @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexProcessor */
        $productEavIndexProcessor = $this->_objectManager->create(\Magento\Catalog\Model\Indexer\Product\Eav\Processor::class);
        $productIds = [];
        $attributes = [];

        /**
         * @var  $key
         * @var array $value
         */
        foreach ($postItems as $key=>$value) {
            try {
                $productId = $value['entity_id'];
                $productIds[] = $productId;
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($productId);
                $flag = true;
                if ($product->getSku() != $value['sku']) {
                    $product->setSku($value['sku']);
                    $flag = false;
                }
                if ($product->getAttributeSetId() != $value['attribute_set_id']) {
                    $product->setAttributeSetId($value['attribute_set_id']);
                    $flag = false;
                }
                if ($product->getTypeId() != $value['type_id']) {
                    $product->setTypeId($value['type_id']);
                    $flag = false;
                }
                if (!$flag) {
                    $this->productRepository->save($product);
                }
                $stockItem = $stockRegistry->getStockItem($key, $storeId);
                if ($stockItem->getQty() != $value['qty']) {
                    $stockItem->setQty($value['qty']);
                    $stockRegistry->updateStockItemBySku($value['sku'], $stockItem);
                }
                foreach ($excludedFields as $excludedField) {
                    unset($value[$excludedField]);
                }
                if (key_exists('price', $value)) {
                    $value['price'] = preg_replace("/[^0-9,\.]/", "", $value['price']);
                }
                if (key_exists('special_price', $value)) {
                    $value['special_price'] = preg_replace("/[^0-9,\.]/", "", $value['special_price']);
                }

                $this->updateAttributeValues($connection, $eavConfig, $productId, $value, $storeId, $error, $attributes);

            } catch (\Exception $e){
                $error[] = "[Product ID {$key}] {$e->getMessage()}";
            }
        }

        try {
            $productEavIndexProcessor->reindexList($productIds);
        } catch (\Exception $e) {
            $error[] = __('Could Not Reindex Product Eav');
        }
    }

    /**
     * @param AdapterInterface $connection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param $productId
     * @param $itemValue
     * @param $storeId
     * @param $error
     * @param array $attributes
     */
    public function updateAttributeValues($connection, $eavConfig, $productId, $itemValue, $storeId, $error, $attributes)
    {
        $localeFormat = $this->_objectManager->get(\Magento\Framework\Locale\FormatInterface::class);
        try {
            //$connection->beginTransaction();
            foreach ($itemValue as $attrCode => $value) {
                if (key_exists($attrCode, $attributes)) {
                    $attribute = $attributes[$attrCode];
                } else {
                    $attribute = $eavConfig->getAttribute('catalog_product', $attrCode);
                    $attributes[$attrCode] = $attribute;
                }
                $connection->insertOnDuplicate(
                    $attribute->getBackend()->getTable(),
                    [
                        'attribute_id' => $attribute->getAttributeId(),
                        'store_id' => $storeId,
                        'entity_id' => $productId,
                        'value' => $this->_prepareValueForSave($connection, $localeFormat, $value, $attribute)
                    ],
                    ['value']
                );
            }
            //$connection->commit();
        }catch (\Exception $e) {
            $connection->rollBack();
            $error[] = "Product ID {$productId}: "  . $e->getMessage();
        }
    }

    /**
     * @param $connection
     * @param $localeFormat
     * @param $value
     * @param AbstractAttribute $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($connection, $localeFormat, $value, AbstractAttribute $attribute)
    {
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $localeFormat->getNumber($value);
        }
        $backendTable = $attribute->getBackendTable();
        if (!isset(self::$_attributeBackendTables[$backendTable])) {
            self::$_attributeBackendTables[$backendTable] = $connection->describeTable($backendTable);
        }
        $describe = self::$_attributeBackendTables[$backendTable];
        return $connection->prepareColumnValue($describe['value'], $value);
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }


    public function assignCategories($items, $error)
    {
        /** @var \Magento\Catalog\Model\CategoryLinkManagement $categoryLinkManagement */
        $categoryLinkManagement = $this->_objectManager->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
        foreach ($items as $key=>$value) {
            $productId = $value['entity_id'];
            try {
                if (!key_exists('category_ids', $value)) {
                    continue;
                }

                $updateCategoryIds = explode(",", $value['category_ids']);

                $categoryLinkManagement->assignProductToCategories($value['sku'], $updateCategoryIds);
            }catch (\Exception $e) {
                $error[] = __("[Product ID {$productId}] {$e->getMessage()}" );
            }
        }

    }

    /**
     * Product access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }
}