<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use MageWorx\OptionTemplates\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionTemplates\Model\ResourceModel\Product as ResourceModelProduct;
use MageWorx\OptionBase\Model\AttributeSaver;
use MageWorx\OptionBase\Model\ResourceModel\DataSaver;

class OptionSaver
{
    const SAVE_MODE_ADD_DELETE = 'add_delete';
    const SAVE_MODE_UPDATE     = 'update';

    const KEY_NEW_PRODUCT      = 'new';
    const KEY_UPD_PRODUCT      = 'upd';
    const KEY_DEL_PRODUCT      = 'del';

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \MageWorx\OptionTemplates\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Array contain all group option ids, that were added to personal product
     *
     * @var array
     */
    protected $productGroupNewOptionIds = [];

    /**
     * @var \MageWorx\OptionTemplates\Model\Group
     */
    protected $group;

    /**
     *
     * @var array
     */
    protected $deletedGroupOptions;

    /**
     *
     * @var array
     */
    protected $addedGroupOptions;

    /**
     *
     * @var array
     */
    protected $intersectedOptions;

    /**
     *
     * @var array
     */
    protected $products = [];

    /**
     * Array of modified options and modified/added option values
     *
     * @var array
     */
    protected $modifiedUpGroupOptions;

    /**
     * Array of deleted option values
     *
     * @var array
     */
    protected $modifiedDownGroupOptions;

    /**
     * Added product option values to template options
     * NEED to be deleted after template re-applying
     * @var array
     */
    protected $addedProductValues;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array|null
     */
    protected $groupOptions;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes
     */
    protected $systemAttributes;

    /**
     * @var array|null
     */
    protected $oldGroupCustomOptions;

    /**
     * @var array
     */
    protected $oldGroupCustomOptionValues;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CollectionUpdaterRegistry
     */
    protected $collectionUpdaterRegistry;

    /**
     * @var ResourceModelProduct
     */
    protected $resourceModelProduct;

    /**
     * @var AttributeSaver
     */
    protected $attributeSaver;

    /**
     * @var \MageWorx\OptionTemplates\Model\OptionSaver\Option
     */
    protected $optionDataCollector;

    /**
     * @var DataSaver
     */
    protected $dataSaver;

    /**
     * @var array
     */
    protected $currentIncrementIds = [];

    /**
     * @var array
     */
    protected $optionData = [];

    /**
     * @var array
     */
    protected $optionsToDelete = [];

    /**
     * @var array
     */
    protected $productsWithOptions = [];

    /**
     * @var array
     */
    protected $linkField = [];

    /**
     *
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory
     * @param OptionRepository $optionRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param ResourceConnection $resource
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param ResourceModelProduct $resourceModelProduct
     * @param \MageWorx\OptionTemplates\Model\OptionSaver\Option $optionDataCollector
     * @param AttributeSaver $attributeSaver
     * @param DataSaver $dataSaver
     */
    public function __construct(
        ReadHandler $readHandler,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory,
        OptionRepository $optionRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        Helper $helper,
        BaseHelper $baseHelper,
        \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes $systemAttributes,
        \MageWorx\OptionBase\Model\Entity\Group $groupEntity,
        \MageWorx\OptionBase\Model\Entity\Product $productEntity,
        ManagerInterface $eventManager,
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        ResourceConnection $resource,
        ResourceModelProduct $resourceModelProduct,
        \MageWorx\OptionTemplates\Model\OptionSaver\Option $optionDataCollector,
        AttributeSaver $attributeSaver,
        DataSaver $dataSaver
    ) {
        $this->readHandler = $readHandler;
        $this->productOptionConfig = $productOptionConfig;
        $this->groupFactory = $groupFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->optionRepository = $optionRepository;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->logger = $logger;
        $this->systemAttributes = $systemAttributes;
        $this->groupEntity = $groupEntity;
        $this->productEntity = $productEntity;
        $this->eventManager = $eventManager;
        $this->collectionUpdaterRegistry = $collectionUpdaterRegistry;
        $this->resource = $resource;
        $this->resourceModelProduct = $resourceModelProduct;
        $this->optionDataCollector = $optionDataCollector;
        $this->attributeSaver = $attributeSaver;
        $this->dataSaver = $dataSaver;
    }

    /**
     * Modify product options using template options
     * Save mode 'add_delete': add template options to new products, delete template options from unassigned products
     * Save mode 'update': similar to 'add_delete' + rewrite template options on existing products
     *
     * @param Group $group
     * @param array $oldGroupCustomOptions
     * @param string $saveMode
     * @return void
     */
    public function saveProductOptions(Group $group, $oldGroupCustomOptions, $saveMode)
    {
        $collection = $this->getAffectedProductsCollection($group);

        if (empty($collection->getItems())) {
            return;
        }

        if ($saveMode == static::SAVE_MODE_UPDATE) {
            /** Reload model for using new option ids **/
            $this->checkPercentPriceOnConfigurable($group);
            /** @var Group group */
            $this->group = $this->groupFactory->create()->load($group->getId());
            $this->grabOptionsDiff($oldGroupCustomOptions);
        } else {
            $this->group = $group;
        }
        $curOptData = [];
        if (count($this->group->getOptions()) > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            foreach ($this->group->getOptions() as $op) {
                if (empty($op->getData('opt_identifier'))) {
                    $opt_identifier = "opt".$op->getData('option_id');
                    $sql = "UPDATE `mageworx_optiontemplates_group_option` SET `opt_identifier` = '".uniqid($opt_identifier)."' WHERE `option_id` = '" . $op->getData('option_id')."'";
                    $connection->query($sql);
                }
                $optId = $op->getData('option_id');
                $curOptData[$optId]['option_id'] = $optId;
                $curOptData[$optId]['is_custom_order'] = $op->getData('is_custom_order');
                $curOptData[$optId]['is_fast_ship'] = $op->getData('is_fast_ship');
                $curOptData[$optId]['all_options_include'] = $op->getData('all_options_include');
                $curOptData[$optId]['all_options_includeco'] = $op->getData('all_options_includeco');
                $curOptData[$optId]['values_id'] = array_keys($op->getValues());
                if (count($op->getValues()) > 0) {
                    foreach ($op->getValues() as $opVal) {                         
                        if (empty($opVal->getData('value_identifier'))) {
                            $value_identifier = $opVal->getData('option_type_id');
                            $sql1 = "UPDATE `mageworx_optiontemplates_group_option_type_value` SET `value_identifier` = '".uniqid($value_identifier)."' WHERE `option_type_id` = '" . $opVal->getData('option_type_id')."'";
                            $connection->query($sql1);
                        }
                    }
                }
            }
        }

        $this->processIncrementIds();

        $products = $this->processProducts($collection, $saveMode);

        $this->saveOptions($products, $curOptData);

        $this->updateHasOptionsStatus();

        return;
    }

    /**
     * Save options by multiple insert
     *
     * @param array $products
     * @return void
     */
    protected function saveOptions($products, $curOptData)
    {
        $this->resource->getConnection()->beginTransaction();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('Magento\Framework\Registry');
        $newAssignedProductIds = $registry->registry('new_assigned_product_ids');
        $logger = $objectManager->create("\Psr\Log\LoggerInterface");
        // $logger->critical("==================== optionData ====================");
        // $logger->critical(json_encode($this->optionData, JSON_PRETTY_PRINT));
        // $logger->critical("====================================================");
        // echo "<pre>";
        // print_r($this->optionData);
        // print_r($curOptData);
        // exit();
        try {
            foreach ($this->optionData as $tableName => $dataItem) {
                if($tableName == 'catalog_product_option') {
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    foreach ($dataItem as $key => $opt) {
                        // $logger->critical("==================== opt ====================");
                        // $logger->critical(json_encode($opt, JSON_PRETTY_PRINT));
                        // $logger->critical("====================================================");
                        $currentPID = $opt['product_id'];
                        $optId = $opt['group_option_id'];
                        $isAllIncluded = 0;
                        $isAllIncludedCO = 0;
                        $updateOpt = 1;
                        if (array_key_exists('is_custom_order', $opt) && array_key_exists('is_fast_ship', $opt) && $opt['group_option_id'] == null) {
                            $updateOpt = 0;
                        } else {
                            $dataItem[$key]['is_custom_order'] = 0;
                            $dataItem[$key]['is_fast_ship'] = 0;
                        }
                        $dataItem[$key]['opt_identifier'] = '';
                        $sqlOptMage = 'SELECT * FROM `mageworx_optiontemplates_group_option` WHERE `option_id` = "'.$optId.'" ORDER BY `option_id` DESC';
                        $resultOptMage = $connection->fetchAll($sqlOptMage);
                        if (count($resultOptMage) > 0) {
                            $dataItem[$key]['opt_identifier'] = $currentPID."-".$resultOptMage[0]['opt_identifier'];
                        }
                        if (count($curOptData) > 0 && array_key_exists($optId, $curOptData)) {
                            $opData = $curOptData[$optId];
                            $optGroupId = '';
                            $getOptData = "SELECT `group_id` FROM `mageworx_optiontemplates_group_option` WHERE `option_id` = '".$optId."'";
                            $resultOptData = $connection->fetchAll($getOptData);
                            if (count($resultOptData) > 0) {
                                $optGroupId = $resultOptData[0]['group_id'];
                            }
                            if ($opData['all_options_include'] == 1 && $updateOpt == 1) {
                                $dataItem[$key]['is_fast_ship'] = $opData['is_fast_ship'];
                                $isAllIncluded = 1;
                            } else if (count($newAssignedProductIds) > 0 && in_array($currentPID, $newAssignedProductIds) && $this->group->getGroupId() == $optGroupId && $updateOpt == 1) {
                                $dataItem[$key]['is_fast_ship'] = $opData['is_fast_ship'];
                                $isAllIncluded = 1;
                            }
                            if ($opData['all_options_includeco'] == 1 && $updateOpt == 1) {
                                $dataItem[$key]['is_custom_order'] = $opData['is_custom_order'];
                                $isAllIncludedCO = 1;
                            } else if (count($newAssignedProductIds) > 0 && in_array($currentPID, $newAssignedProductIds) && $this->group->getGroupId() == $optGroupId && $updateOpt == 1) {
                                $dataItem[$key]['is_custom_order'] = $opData['is_custom_order'];
                                $isAllIncludedCO = 1;
                            }
                        }
                        if ($isAllIncluded == 0 && $updateOpt == 1) {
                            $resultOptIdentifier = [];
                            if ($dataItem[$key]['opt_identifier'] != '') {
                                $sql1 = 'SELECT * FROM `catalog_product_option` WHERE `opt_identifier` = "'.$dataItem[$key]['opt_identifier'].'" ORDER BY `option_id` ASC';
                                $resultOptIdentifier = $connection->fetchAll($sql1);
                                $getData = "SELECT * FROM `catalog_product_option` WHERE `product_id` = '".$currentPID."' AND `opt_identifier` ='".$dataItem[$key]['opt_identifier']."'";
                                $resultOptIdentifier = $connection->fetchAll($getData);
                            }
                            if (count($resultOptIdentifier) > 0) {
                                $dataItem[$key]['is_fast_ship'] = $resultOptIdentifier[0]['is_fast_ship'];
                            }
                        }

                        if ($isAllIncludedCO == 0 && $updateOpt == 1) {
                            $resultOptIdentifier = [];
                            if ($dataItem[$key]['opt_identifier'] != '') {
                                $sql1 = 'SELECT * FROM `catalog_product_option` WHERE `opt_identifier` = "'.$dataItem[$key]['opt_identifier'].'" ORDER BY `option_id` ASC';
                                $resultOptIdentifier = $connection->fetchAll($sql1);
                                $getData = "SELECT * FROM `catalog_product_option` WHERE `product_id` = '".$currentPID."' AND `opt_identifier` ='".$dataItem[$key]['opt_identifier']."'";
                                $resultOptIdentifier = $connection->fetchAll($getData);
                            }
                            if (count($resultOptIdentifier) > 0) {
                                $dataItem[$key]['is_custom_order'] = $resultOptIdentifier[0]['is_custom_order'];
                            }
                        } 
                    }
                    $this->optionData[$tableName] = $dataItem;
                } else if($tableName == 'catalog_product_option_type_value') {
                    foreach ($dataItem as $key => $value) {
                        // $logger->critical("==================== dataItem ====================");
                        // $logger->critical(json_encode($dataItem, JSON_PRETTY_PRINT));
                        // $logger->critical(json_encode($curOptData, JSON_PRETTY_PRINT));
                        // $logger->critical("====================================================");
                        if (!array_key_exists('is_stocktab', $value)) {
                            $dataItem[$key]['is_stocktab'] = 0;
                        }
                        if (!array_key_exists('is_customtab', $value)) {
                            $dataItem[$key]['is_customtab'] = 0;
                        }
                        $currentPID = NULL;
                        if (array_key_exists('catalog_product_option', $this->optionData) && array_key_exists($value['option_id'], $this->optionData['catalog_product_option'])) {
                            $currentPID = $this->optionData['catalog_product_option'][$value['option_id']]['product_id'];
                        }
                        $dataItem[$key]['value_identifier'] = '';
                        if (isset($value['group_option_value_id'])) {
                            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                            $connection = $resource->getConnection();
                            $sql = 'SELECT * FROM `mageworx_optiontemplates_group_option_type_value` WHERE `option_type_id` = '.$value['group_option_value_id'].' ORDER BY `option_type_id` DESC';
                            $result = $connection->fetchAll($sql);
                            if (count($result) > 0) {
                                $dataItem[$key]['value_identifier'] = $currentPID."-".$result[0]['value_identifier'];
                            }   
                        }
                        if (!array_key_exists('weight', $value)) {
                            $dataItem[$key]['weight'] = '0.00';
                        }
                        if (count($curOptData) > 0) {
                            $optId = '';
                            $isAllIncluded = 0;
                            $isAllIncludedCO = 0;
                            foreach ($curOptData as $k => $v) {
                                if (in_array($value['group_option_value_id'], $v['values_id'])) {
                                    $optId = $k;     
                                } 
                            }
                            if ($optId != '') {
                                $opData = $curOptData[$optId];
                                if ($opData['all_options_include'] == 1) {
                                    $dataItem[$key]['is_stocktab'] = $opData['is_fast_ship'];
                                    $isAllIncluded = 1;
                                } else if (count($newAssignedProductIds) > 0 && in_array($currentPID, $newAssignedProductIds)) {
                                    $dataItem[$key]['is_stocktab'] = $opData['is_fast_ship'];
                                    $isAllIncluded = 1;
                                }

                                if ($opData['all_options_includeco'] == 1) {
                                    $dataItem[$key]['is_customtab'] = $opData['is_custom_order'];
                                    $isAllIncludedCO = 1;
                                } else if (count($newAssignedProductIds) > 0 && in_array($currentPID, $newAssignedProductIds)) {
                                    $dataItem[$key]['is_customtab'] = $opData['is_custom_order'];
                                    $isAllIncludedCO = 1;
                                }
                            }
                        }
                        if ($isAllIncluded == 0) {
                            $result1 = [];
                            if ($dataItem[$key]['value_identifier'] != '') {
                                $sql1 = 'SELECT * FROM `catalog_product_option_type_value` WHERE `value_identifier` = "'.$dataItem[$key]['value_identifier'].'" ORDER BY `option_type_id` ASC';
                                $result1 = $connection->fetchAll($sql1);
                            }
                            if (count($result1) > 0) {
                                $dataItem[$key]['is_stocktab'] = $result1[0]['is_stocktab'];
                            }
                        }
                        if ($isAllIncludedCO == 0) {
                            $result1 = [];
                            if ($dataItem[$key]['value_identifier'] != '') {
                                $sql1 = 'SELECT * FROM `catalog_product_option_type_value` WHERE `value_identifier` = "'.$dataItem[$key]['value_identifier'].'" ORDER BY `option_type_id` ASC';
                                $result1 = $connection->fetchAll($sql1);
                            }
                            if (count($result1) > 0) {
                                $dataItem[$key]['is_customtab'] = $result1[0]['is_customtab'];
                            }
                        }
                    }
                    $this->optionData[$tableName] = $dataItem;
                }
            }
            if ($this->optionsToDelete) {
                $condition = 'option_id IN ('. implode(',', $this->optionsToDelete) .')';
                $this->dataSaver->deleteData('catalog_product_option', $condition);
                $condition = 'option_id IN ('. implode(',', $this->optionsToDelete) .')';
                $this->dataSaver->deleteData('catalog_product_option_type_value', $condition);
            }

            //saving custom options to products
            foreach ($this->optionData as $tableName => $dataItem) {
                $this->dataSaver->insertMultipleData($tableName, $dataItem);
            }
            $registry->unregister('new_assigned_product_ids');

            $this->linkField = $this->baseHelper->getLinkField(ProductInterface::class);
            $this->productsWithOptions = [];
            foreach ($products as $productItem) {
                $this->updateProductData($productItem);
                $this->doProductRelationAction($productItem->getId());
            }

            //saving APO attributes to products
            $collectedData = $this->attributeSaver->getAttributeData();
            $this->attributeSaver->deleteOldAttributeData($collectedData, 'product');
            foreach ($collectedData as $tableName => $dataArray) {
                if (empty($dataArray['save'])) {
                    continue;
                }
                $this->dataSaver->insertMultipleData($tableName, $dataArray['save']);
            }
            $this->resource->getConnection()->commit();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->resource->getConnection()->rollBack();
        }
        $this->attributeSaver->clearAttributeData();
    }

    /**
     * Get product collection using selected product IDs
     *
     * @param Group $group
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    protected function getAffectedProductsCollection($group)
    {
        $this->products[self::KEY_NEW_PRODUCT] = $group->getNewProductIds();
        $this->products[self::KEY_UPD_PRODUCT] = $group->getUpdProductIds();
        $this->products[self::KEY_DEL_PRODUCT] = $group->getDelProductIds();
        $allProductIds = $group->getAffectedProductIds();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $this->collectionUpdaterRegistry->setCurrentEntityType('product');
        $this->collectionUpdaterRegistry->setCurrentEntityId(0);
        $collection->addStoreFilter(0)
                   ->setStoreId(0)
                   ->addFieldToFilter('entity_id', ['in' => $allProductIds])
                   ->addOptionsToResult();
        return $collection;
    }

    /**
     * Collect difference between old template options and the new one
     *
     * @param array $oldGroupCustomOptions
     * @return void
     */
    protected function grabOptionsDiff($oldGroupCustomOptions)
    {
        $this->groupOptions = $this->groupEntity->getOptionsAsArray($this->group);
        $this->oldGroupCustomOptions = $oldGroupCustomOptions;
        $this->oldGroupCustomOptionValues = $this->getOptionValues($this->oldGroupCustomOptions);
        $this->deletedGroupOptions = $this->getGroupDeletedOptions();
        $this->addedGroupOptions = $this->getGroupAddedOptions();
        $this->intersectedOptions = $this->getGroupIntersectedOptions();
        $groupNewModifiedOptions = $this->getGroupNewModifiedOptions();
        $groupLastModifiedOptions = $this->getGroupLastModifiedOptions();
        $this->modifiedUpGroupOptions = $this->arrayDiffRecursive(
            $groupNewModifiedOptions,
            $groupLastModifiedOptions
        );
        $this->modifiedDownGroupOptions = $this->arrayDiffRecursive(
            $groupLastModifiedOptions,
            $groupNewModifiedOptions
        );
    }

    /**
     * Try to collect current increment IDs for option and values and throw error if something wrong
     *
     * @return void
     */
    protected function processIncrementIds()
    {
        try {
            $this->collectCurrentIncrementIds();
            if (empty($this->currentIncrementIds['option']) || empty($this->currentIncrementIds['value'])) {
                throw new Exception(__("Can't get current auto_increment ID"));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getLogMessage());
        }
    }

    /**
     * Collect current increment IDs for option and values
     *
     * @return void
     */
    protected function collectCurrentIncrementIds()
    {
        $this->currentIncrementIds = [];

        $optionTableStatus = $this->resource->getConnection()->showTableStatus(
            $this->resource->getTableName('catalog_product_option')
        );
        if (!empty($optionTableStatus['Auto_increment'])) {
            $this->currentIncrementIds['option'] = $optionTableStatus['Auto_increment'];
        }

        $valueTableStatus = $this->resource->getConnection()->showTableStatus(
            $this->resource->getTableName('catalog_product_option_type_value')
        );
        if (!empty($valueTableStatus['Auto_increment'])) {
            $this->currentIncrementIds['value'] = $valueTableStatus['Auto_increment'];
        }
    }

    /**
     * Process template-to-product relation changes and collect default magento data from options
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param string $saveMode
     * @return \Magento\Catalog\Model\Product[]
     */
    protected function processProducts($collection, $saveMode)
    {
        $this->optionData = [];
        $this->optionsToDelete = [];
        $products = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $customOptions = [];
            $this->clearProductGroupNewOptionIds();
            $product->setStoreId(0);
            $preparedProductOptionArray = $this->getPreparedProductOptions($product, $saveMode);

            try {
                foreach ($preparedProductOptionArray as $preparedOption) {
                    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
                    if (is_object($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption->getData()]);
                        $id = $preparedOption->getData('id');
                        $values = $preparedOption->getValues();
                    } elseif (is_array($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption]);
                        $id = $preparedOption['id'];
                        $values = !empty($preparedOption['values']) ? $preparedOption['values'] : [];
                    } else {
                        throw new Exception(
                            __(
                                'The prepared option is not an instance of DataObject or array. %1 is received',
                               gettype($preparedOption)
                            )
                        );
                    }

                    $customOption->setProductSku($product->getSku())
                                 ->setOptionId($id)
                                 ->setValues($values);
                    $customOptions[] = $customOption;
                }
                if (!empty($customOptions)) {
                    $product->setOptions($customOptions);
                    $product->setCanSaveCustomOptions(true);
                    $this->optionDataCollector->collectOptionsBeforeInsert(
                        $product,
                        $this->optionData,
                        $this->currentIncrementIds,
                        $this->optionsToDelete
                    );
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
            $products[] = $product;
        }
        return $products;
    }

    /**
     * Get values from options
     *
     * @param array|null $options
     * @return array $values
     */
    protected function getOptionValues($options)
    {
        $values = [];
        if (empty($options)) {
            return $values;
        }

        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $valueKey => $value) {
                $values[$valueKey] = $value;
            }
        }
        return $values;
    }


    /**
     * Transfer product based custom options attributes from group to the corresponding product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function updateProductData($product)
    {
        $excludeAttributes = $this->systemAttributes->toArray();
        $groupData = $this->group->getData();
        foreach ($excludeAttributes as $attribute) {
            unset($groupData[$attribute]);
        }

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $this->readHandler->execute($product);
        }

        $product->addData($groupData);
        if ($product->getOptions()) {
            $this->productsWithOptions[] = $product->getData($this->linkField);
            $product->setHasOptions(1);
        }

        $product->setIsAfterTemplateSave(true);

        $this->eventManager->dispatch(
            'mageworx_attributes_save_trigger',
            ['product' => $product, 'after_template' => true]
        );
    }

    /**
     * Update has_options flag in products
     *
     * @return void
     */
    protected function updateHasOptionsStatus()
    {
        if (empty($this->productsWithOptions)) {
            return;
        }
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('catalog_product_entity');
        $data = [
            'has_options' => 1,
        ];
        $sql = $this->linkField." IN (".implode(',', $this->productsWithOptions).")";
        $connection->update($tableName, $data, $sql);
    }

    /**
     * Check percentage options restriction on configurable products
     *
     * @param Group $group
     * @throws LocalizedException
     */
    protected function checkPercentPriceOnConfigurable($group)
    {
        if (empty($group->getData('options'))) {
            return;
        }
        $isPercentTypeExist = false;
        foreach ($group->getData('options') as $option) {
            if (isset($option['price_type']) && $option['price_type'] == 'percent') {
                $isPercentTypeExist = true;
                break;
            }
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (isset($value['price_type']) && $value['price_type'] == 'percent') {
                    $isPercentTypeExist = true;
                    break;
                }
            }
        }
        if (!$isPercentTypeExist) {
            return;
        }

        $newAndUpdatedProductIds = [];
        foreach ($this->products[self::KEY_NEW_PRODUCT] as $productId) {
            $newAndUpdatedProductIds[] = $productId;
        }
        foreach ($this->products[self::KEY_UPD_PRODUCT] as $productId) {
            $newAndUpdatedProductIds[] = $productId;
        }
        if ($newAndUpdatedProductIds) {
            $newAndUpdatedProducts = $this->resourceModelProduct->getProductsByIds($newAndUpdatedProductIds);
            foreach ($newAndUpdatedProducts as $newAndUpdatedProduct) {
                if (isset($newAndUpdatedProduct['type_id'])
                    && $newAndUpdatedProduct['type_id'] == Configurable::TYPE_CODE
                ) {
                    $message = 'Custom options with percentage price type '.
                        'could not be saved on assigned configurable products, ' .
                        'because Magento 2 does not allow saving percentage options on configurable items. ' .
                        'Please, do not assign configurable products to template ' .
                        'or change price types from "percent" to "fixed"';
                    throw new LocalizedException(__($message));
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function clearProductGroupNewOptionIds()
    {
        $this->productGroupNewOptionIds = [];
    }

    /**
     *
     * @return array
     */
    protected function getGroupDeletedOptions()
    {
        return array_diff_key($this->oldGroupCustomOptions, $this->groupOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupAddedOptions()
    {
        return array_diff_key($this->groupOptions, $this->oldGroupCustomOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupIntersectedOptions()
    {
        return array_intersect_key($this->groupOptions, $this->oldGroupCustomOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupNewModifiedOptions()
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($this->oldGroupCustomOptions));
        $prepareNewGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($this->groupOptions[$optionId])) {
                $prepareNewGroupOptions[$optionId] = $this->groupOptions[$optionId];
            }
        }

        return $prepareNewGroupOptions;
    }

    /**
     *
     * @return array
     */
    protected function getGroupLastModifiedOptions()
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($this->oldGroupCustomOptions));
        $prepareLastGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($this->oldGroupCustomOptions[$optionId])) {
                $prepareLastGroupOptions[$optionId] = $this->oldGroupCustomOptions[$optionId];
            }
        }

        return $prepareLastGroupOptions;
    }

    /**
     * Retrieve new product options as array, that were built by group modification
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $saveMode
     * @return array
     */
    protected function getPreparedProductOptions($product, $saveMode)
    {
        $productOptions = $this->productEntity->getOptionsAsArray($product);

        if ($saveMode == static::SAVE_MODE_UPDATE) {
            $ids = [];
            foreach ($this->groupOptions as $groupKey => $groupValue) {
                $ids[$groupKey] = $groupValue;
            }
            foreach ($productOptions as $productOption) {
                if (empty($ids[$productOption['group_option_id']]) || empty($productOption['values'])) {
                    continue;
                }
                foreach ($productOption['values'] as $valueKey => $valueData) {
                    if (empty($valueData['group_option_value_id'])) {
                        $this->addedProductValues[$productOption['group_option_id']]['values'][$valueKey] = $valueData;
                    }
                }
            }

            if ($this->isNewProduct($product->getId())) {
                $productOptions = $this->addNewOptionProcess($productOptions);
            } elseif ($this->isUpdProduct($product->getId())) {
                $productOptions = $this->deleteOptionProcess($productOptions);
                $productOptions = $this->addNewOptionProcess($productOptions);
                $productOptions = $this->modifyOptionProcess($productOptions);
            } elseif ($this->isDelProduct($product->getId())) {
                $productOptions = $this->deleteOptionProcess($productOptions);
            }
        } else {
            if ($this->isNewProduct($product->getId())) {
                $productOptions = $this->addNewOptionProcess($productOptions, $this->group);
            } elseif ($this->isDelProduct($product->getId())) {
                $productOptions = $this->deleteOptionProcess($productOptions, $this->group);
            }
        }

        return $productOptions;
    }

    /**
     * Delete options that were deleted in group
     *
     * @todo Delete All product option with group_option_id that missed in group.
     * @param array $productOptions
     * @param null $group
     * @return array
     */
    public function deleteOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $deletedGroupOptionIds = array_keys($this->deletedGroupOptions);
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
            $deletedGroupOptionIds = array_keys($groupOptions);
        }

        foreach ($productOptions as $optionIndex => $productOption) {
            if (!empty($productOption['group_option_id']) &&
                in_array($productOption['group_option_id'], $deletedGroupOptionIds)
            ) {
                $productOption['is_delete'] = '1';
                $productOptions[$optionIndex] = $productOption;
            }
        }

        return $productOptions;
    }

    /**
     * Delete all group options
     *
     * @param array $productOptions
     * @return array
     */
    protected function clearOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $key => $productOption) {
            if (empty($productOption['group_option_id'])) {
                continue;
            }
            foreach ($this->group->getOptions() as $option) {
                if ($productOption['group_option_id'] == $option->getData('option_id')) {
                    $productOptions[$key]['is_delete'] = '1';
                }
            }
        }

        return $productOptions;
    }

    /**
     * Modify options that were modified in group
     *
     * @param array $productOptions
     * @return array
     */
    protected function modifyOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $productOptionId => $productOption) {
            $groupOptionId = !empty($productOption['group_option_id']) ? $productOption['group_option_id'] : null;
            if (!$groupOptionId) {
                continue;
            }
            if ($this->isOptionWereRecreated($groupOptionId)) {
                continue;
            }
            if (!empty($this->modifiedDownGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedDownGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            $productOptions[$productOptionId][$modPropertyKey] = [];
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            //Option value were deleted in group - delete it in corresponding product option
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->markProductOptionValueAsDelete(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueData['option_type_id'],
                                        'group_option_value_id'
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } elseif (!is_array($modProperty)) {
                        unset($productOptions[$productOptionId][$modPropertyKey]);
                    }
                }
            }

            if (!empty($this->modifiedUpGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedUpGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            $productOptions[$productOptionId][$modPropertyKey] = [];
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey][] =
                                    $this->convertGroupOptionValueToProductOptionValue(
                                        $valueData,
                                        $productOptionId,
                                        $productOptions[$productOptionId][$modPropertyKey]
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } elseif (!is_array($modProperty)) {
                        $productOptions[$productOptionId][$modPropertyKey] = $modProperty;
                    }
                }
            }

            if (!empty($this->addedProductValues[$groupOptionId])) {
                foreach ($this->addedProductValues[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            continue;
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            //delete product option value that was added to template option
                            if (empty($valueData['option_type_id'])) {
                                continue;
                            }
                            $productOptions[$productOptionId][$modPropertyKey] =
                                $this->markProductOptionValueAsDelete(
                                    $productOptions[$productOptionId][$modPropertyKey],
                                    $valueData['option_type_id'],
                                    'option_type_id'
                                );
                        }
                    }
                }
            }
        }

        return $productOptions;
    }

    /**
     * Add new options that were added in group
     *
     * @param array $productOptions
     * @param Group|null
     * @return array
     */
    public function addNewOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $groupOptions = $this->groupOptions;
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
        }

        $newProductOptions = [];

        $i = $productOptions ? max(array_keys($productOptions)) + 1 : 1;

        foreach ($groupOptions as $groupOption) {
            $issetGroupOptionInProduct = false;

            foreach ($productOptions as $optionIndex => $productOption) {
                if (!empty($productOption['group_option_id'])
                    && $productOption['group_option_id'] == $groupOption['option_id']
                ) {
                    $issetGroupOptionInProduct = true;
                }
            }

            if (!$issetGroupOptionInProduct) {
                $groupOption['group_option_id'] = $groupOption['id'];
                $groupOption['id'] = (string)$i;
                $groupOption['option_id'] = '0';

                $groupOption = $this->convertGroupToProductOptionValues($groupOption);
                $newProductOptions[$i] = $groupOption;
                $this->productGroupNewOptionIds[] = $groupOption['group_option_id'];
            }
            $i++;
        }

        return $productOptions + $newProductOptions;
    }

    /**
     * Unassign options from template
     *
     * @param array $productOptions
     * @param Group $group
     * @return array
     */
    public function unassignOptions(array $productOptions, $group)
    {
        $groupOptions = $this->groupEntity->getOptionsAsArray($group);
        $deletedGroupOptionIds = array_keys($groupOptions);

        foreach ($productOptions as $optionIndex => $productOption) {
            if (empty($productOption['group_option_id']) ||
                !in_array($productOption['group_option_id'], $deletedGroupOptionIds)
            ) {
                continue;
            }
            $productOptions[$optionIndex]['group_option_id'] = null;
            if (empty($productOption['values']) || !is_array($productOption['values'])) {
                continue;
            }
            foreach ($productOption['values'] as $valueIndex => $valueData) {
                $productOptions[$optionIndex]['values'][$valueIndex]['group_option_value_id'] = null;
            }
        }

        return $productOptions;
    }

    /**
     *
     * @param array $option
     * @return array
     */
    protected function convertGroupToProductOptionValues($option)
    {
        if (!empty($option['values'])) {
            foreach ($option['values'] as $valueKey => $value) {
                $value['group_option_value_id'] = $value['option_type_id'];
                $value['option_type_id'] = '-1';
                $option['values'][$valueKey] = $value;
            }
        }

        return $option;
    }

    /**
     *
     * @param int $productId
     */
    protected function doProductRelationAction($productId)
    {
        if ($this->isNewProduct($productId)) {
            $this->group->addProductRelation($productId);
        } elseif ($this->isDelProduct($productId)) {
            $this->group->deleteProductRelation($productId);
        }
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isNewProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_NEW_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isUpdProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_UPD_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isDelProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_DEL_PRODUCT]);
    }

    /**
     * Check if different options types
     *
     * @param string $typeOld
     * @param string $typeNew
     * @return bool
     */
    protected function isSameOptionGroupType($typeOld, $typeNew)
    {
        return ($this->getOptionGroupType($typeOld) == $this->getOptionGroupType($typeNew));
    }

    /**
     *
     * @param string $name
     * @return string
     */
    protected function getOptionGroupType($name)
    {
        foreach ($this->productOptionConfig->getAll() as $typeName => $data) {
            if (!empty($data['types'][$name])) {
                return $typeName;
            }
        }

        return null;
    }

    /**
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function arrayDiffRecursive($arr1, $arr2)
    {
        $outputDiff = [];

        foreach ($arr1 as $key => $value) {
            if (is_array($arr2) && array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayDiffRecursive($value, $arr2[$key]);
                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                } elseif ($arr2[$key] != $value) {
                    $outputDiff[$key] = $value;
                }
            } else {
                $outputDiff[$key] = $value;
            }
        }

        return $outputDiff;
    }

    /**
     * Check if option was recreated
     *
     * @param string $groupOptionId
     * @return bool
     */
    protected function isOptionWereRecreated($groupOptionId)
    {
        return in_array($groupOptionId, $this->productGroupNewOptionIds);
    }

    /**
     * Convert group option value to product option value, keep changed attributes from config (qty, for example)
     *
     * @param array $groupOptionValueData
     * @param int $productOptionId
     * @param array $productOptionValues
     * @return string
     */
    protected function convertGroupOptionValueToProductOptionValue(array $groupOptionValueData, $productOptionId, $productOptionValues)
    {
        $groupOptionValueData['option_id'] = (string)$productOptionId;
        $groupOptionValueData['group_option_value_id'] = $groupOptionValueData['option_type_id'];
        $groupOptionValueData['option_type_id'] = '-1';

        foreach ($productOptionValues as $optionValue) {
            if (empty($optionValue['group_option_value_id'])) {
                continue;
            }
            if (empty($this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']])) {
                continue;
            }
            if (empty($this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']]['mageworx_group_option_type_id'])) {
                continue;
            }
            $linkedMageworxOptionId = $this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']]['mageworx_group_option_type_id'];
            if ($linkedMageworxOptionId != $groupOptionValueData['mageworx_group_option_type_id']) {
                continue;
            }
            foreach ($this->helper->getReapplyExceptionAttributeKeys() as $attribute) {
                if (!isset($optionValue[$attribute])) {
                    continue;
                }
                $oldOptionValueData = $this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']][$attribute];
                if ($oldOptionValueData == $optionValue[$attribute]) {
                    continue;
                }
                $groupOptionValueData[$attribute] = $optionValue[$attribute];
            }
        }

        return $groupOptionValueData;
    }

    /**
     * Mark 'delete' a product option value by deleted group option value
     *
     * @param array $productOptionValueArray
     * @param int $valueId
     * @param string $linkKey
     * @return array
     */
    protected function markProductOptionValueAsDelete(array $productOptionValueArray, $valueId, $linkKey)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionData) {
            if (!empty($optionData[$linkKey]) &&
                $valueId == $optionData[$linkKey]
            ) {
                $productOptionValueArray[$optionValueKey]['is_delete'] = '1';
                break;
            }
        }

        return $productOptionValueArray;
    }

    /**
     * Modify/add product option value properties by modified group option value properties
     *
     *
     * @param array $productOptionValueArray
     * @param int $groupOptionValueId
     * @param array $valueData
     * @return array
     */
    protected function getModifyProductOptionValue(array $productOptionValueArray, $groupOptionValueId, $valueData)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionValue) {
            if (!empty($optionValue['group_option_value_id']) &&
                $groupOptionValueId == $optionValue['group_option_value_id']
            ) {
                foreach ($valueData as $key => $value) {
                    $productOptionValueArray[$optionValueKey][$key] = $value;
                }
                break;
            }
        }

        return $productOptionValueArray;
    }
}
