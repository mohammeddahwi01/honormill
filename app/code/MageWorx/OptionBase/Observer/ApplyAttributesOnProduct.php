<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionValueCollection;
use \MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use \MageWorx\OptionBase\Model\Entity\Product as ProductEntity;
use \MageWorx\OptionBase\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeSaver;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use MageWorx\OptionBase\Model\ResourceModel\DataSaver;

class ApplyAttributesOnProduct implements ObserverInterface
{
    /**
     * @var OptionValueCollection
     */
    protected $optionValueCollection;

    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var ProductEntity
     */
    protected $productEntity;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var AttributeSaver
     */
    protected $attributeSaver;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DataSaver
     */
    protected $dataSaver;

    /**
     * Product options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Product ID
     *
     * @var integer|null
     */
    protected $productId = null;

    /**
     * @param OptionValueCollection $optionValueCollection
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param Product $productModel
     * @param ProductEntity $productEntity
     * @param Helper $helper
     * @param ResourceConnection $resource
     * @param Logger $logger
     * @param MessageManager $messageManager
     * @param AttributeSaver $attributeSaver
     * @param DataSaver $dataSaver
     */
    public function __construct(
        OptionValueCollection $optionValueCollection,
        ProductAttributes $productAttributes,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        Product $productModel,
        ProductEntity $productEntity,
        Helper $helper,
        ResourceConnection $resource,
        Logger $logger,
        MessageManager $messageManager,
        AttributeSaver $attributeSaver,
        DataSaver $dataSaver
    ) {
        $this->optionValueCollection = $optionValueCollection;
        $this->productAttributes = $productAttributes;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->productModel = $productModel;
        $this->productEntity = $productEntity;
        $this->helper = $helper;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->attributeSaver = $attributeSaver;
        $this->dataSaver = $dataSaver;
    }

    /**
     * Save option value description
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product) {
            return;
        }
        $afterTemplate = $observer->getAfterTemplate();

        $this->initProductId($observer);
        $this->initOptions($observer);

        $this->productEntity->setDataObject($product);

        $attributes = $this->productAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->productEntity);
        }

        $optionValueAttributes = $this->optionValueAttributes->getData();
        $this->collectAttributeData($optionValueAttributes);
        $optionAttributes = $this->optionAttributes->getData();
        $this->collectAttributeData($optionAttributes);

        if ($afterTemplate) {
            return;
        }

        // save product data if it is not from template
        $this->resource->getConnection()->beginTransaction();
        try {
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
            $this->messageManager->addExceptionMessage(
                $e,
                __("Something went wrong while saving product's APO attributes")
            );
            $this->logger->critical($e->getMessage());
            $this->resource->getConnection()->rollBack();
        }
        $this->attributeSaver->clearAttributeData();
    }

    /**
     * Collect attribute data
     *
     * @param array $attributes
     * @return void
     */
    protected function collectAttributeData($attributes)
    {
        foreach ($attributes as $attribute) {
            $data = [];
            if (!$attribute->hasOwnTable()) {
                continue;
            }
            $attributeItemData = $attribute->collectData($this->productEntity, $this->options);
            if (!$attributeItemData) {
                continue;
            }
            $tableName = $this->resource->getTableName($attribute->getTableName('product'));

            if (!empty($attributeItemData['save'])) {
                foreach ($attributeItemData['save'] as $attributeItemDataItem) {
                    $data['save'][] = $attributeItemDataItem;
                }
            }
            if (!empty($attributeItemData['delete'])) {
                foreach ($attributeItemData['delete'] as $attributeItemDataItem) {
                    $data['delete'][] = $attributeItemDataItem;
                }
            }
            $this->attributeSaver->addAttributeData($tableName, $data);
        }
    }

    /**
     * @param Observer $observer
     * @return void
     */
    protected function initProductId($observer)
    {
        $this->productId = $observer->getEvent()->getProduct()->getId();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    protected function initOptions($observer)
    {
        $currentOptions = $observer->getProduct()->getOptions();
        if ($observer->getProduct()->getIsAfterTemplateSave()) {
            $this->options = [];
            if (empty($currentOptions)) {
                return;
            }
            foreach ($currentOptions as $currentOption) {
                if (!empty($currentOption['is_delete'])) {
                    continue;
                }
                $this->options[] = $currentOption;
            }
        } else {
            $savedOptions = $this->productModel->load($observer->getProduct()->getId())->getOptions();

            $currentOptions = $this->helper->beatifyOptions($currentOptions);
            $savedOptions = $this->helper->beatifyOptions($savedOptions);

            $this->options = $this->mergeArrays($currentOptions, $savedOptions);
        }
    }

    /**
     * Merge current and saved arrays
     *
     * @param array $current
     * @param array $saved
     * @return array
     */
    protected function mergeArrays($current, $saved)
    {
        foreach ($current as $currentOption) {
            if (!empty($currentOption['is_delete'])) {
                continue;
            }
            $currentOptionId = $currentOption['option_id'];
            $currentOptionRecordId = isset($currentOption['record_id'])
                ? $currentOption['record_id']
                : $currentOption['option_id'];

            $currentOptionAttributes = [];
            $optionAttributes = $this->optionAttributes->getData();
            foreach ($optionAttributes as $optionAttribute) {
                $currentOptionAttributes[] = $optionAttribute->getName();
            }
            // set data to option $saved
            $savedOptionKey = $this->helper->searchArray('option_id', $currentOptionId, $saved);
            if ($savedOptionKey === null) {
                continue;
            }
            $saved[$savedOptionKey]['record_id'] = $currentOptionRecordId;
            foreach ($currentOptionAttributes as $currentOptionAttribute) {
                if (!isset($currentOption[$currentOptionAttribute])) {
                    continue;
                }
                $saved[$savedOptionKey][$currentOptionAttribute] = $currentOption[$currentOptionAttribute];
            }

            if (!empty($currentOption['group_id'])) {
                $saved[$savedOptionKey]['group_id'] = $currentOption['group_id'];
            }

            $currentValues = isset($currentOption['values']) ? $currentOption['values'] : [];
            foreach ($currentValues as $currentValue) {
                $currentValueSortOrder = $currentValue['sort_order'];
                $currentValueRecordId = isset($currentValue['record_id'])
                    ? $currentValue['record_id']
                    : $currentValue['option_id'];

                $currentValueAttributes = [];
                $valueAttributes = $this->optionValueAttributes->getData();
                foreach ($valueAttributes as $valueAttribute) {
                    $currentValueAttributes[] = $valueAttribute->getName();
                }

                // set data to option $saved
                $savedValueKey = $this->helper->searchArray(
                    'sort_order',
                    $currentValueSortOrder,
                    $saved[$savedOptionKey]['values']
                );
                if ($savedValueKey === null) {
                    continue;
                }

                $optionValue = &$saved[$savedOptionKey]['values'][$savedValueKey];
                if (!empty($currentValue['group_id'])) {
                    $optionValue['group_id'] = $currentValue['group_id'];
                }
                $optionValue['record_id'] = $currentValueRecordId;
                $optionValue['mageworx_option_id'] = $saved[$savedOptionKey]['mageworx_option_id'];
                foreach ($currentValueAttributes as $currentValueAttribute) {
                    if (!isset($currentValue[$currentValueAttribute])) {
                        continue;
                    }
                    $optionValue[$currentValueAttribute] = $currentValue[$currentValueAttribute];
                }
            }
        }

        return $saved;
    }
}
