<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionValueCollection;
use \MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use \MageWorx\OptionBase\Model\Entity\Group as GroupEntity;
use \Magento\Framework\Model\AbstractModel as Group;
use \MageWorx\OptionBase\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeSaver;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use MageWorx\OptionBase\Model\ResourceModel\DataSaver;

class ApplyAttributesOnGroup implements ObserverInterface
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
     * @var GroupEntity
     */
    protected $groupEntity;

    /**
     * @var Group
     */
    protected $groupModel;

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
     * Group options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Group ID
     *
     * @var integer|null
     */
    protected $groupId = null;

    /**
     * @param OptionValueCollection $optionValueCollection
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param Group $groupModel
     * @param GroupEntity $groupEntity
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
        Group $groupModel,
        GroupEntity $groupEntity,
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
        $this->groupEntity = $groupEntity;
        $this->groupModel = $groupModel;
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
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->initGroupId($observer);
        $this->initOptions($observer);

        $group = $observer->getGroup();
        $options = $group->getData('options');
        if (empty($options)) {
            return $this;
        }

        $this->groupEntity->setDataObject($group);

        $attributes = $this->productAttributes->getData();
        foreach ($attributes as $attribute) {
            $attribute->applyData($this->groupEntity);
        }

        $optionValueAttributes = $this->optionValueAttributes->getData();
        $this->collectAttributeData($optionValueAttributes);
        $optionAttributes = $this->optionAttributes->getData();
        $this->collectAttributeData($optionAttributes);

        $this->resource->getConnection()->beginTransaction();
        try {
            $collectedData = $this->attributeSaver->getAttributeData();
            $this->attributeSaver->deleteOldAttributeData($collectedData, 'group');

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
                __("Something went wrong while saving group's APO attributes")
            );
            $this->logger->critical($e->getMessage());
            $this->resource->getConnection()->rollBack();
        }
        $this->attributeSaver->clearAttributeData();

        return $this;
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
            $attributeItemData = $attribute->collectData($this->groupEntity, $this->options);
            if (!$attributeItemData) {
                continue;
            }
            $tableName = $this->resource->getTableName($attribute->getTableName('group'));

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
     * @return $this
     */
    protected function initGroupId($observer)
    {
        $this->groupId = $observer->getGroup()->getGroupId();
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    protected function initOptions($observer)
    {
        $currentOptions = $observer->getGroup()->getData('options');
        $savedOptions = $this->groupModel->load($this->groupId)->getOptions();

        $currentOptions = $this->helper->beatifyOptions($currentOptions);
        $savedOptions = $this->helper->beatifyOptions($savedOptions);

        $this->options = $this->mergeArrays($currentOptions, $savedOptions);
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
            $currentOptionSortOrder = $currentOption['sort_order'];
            $currentOptionRecordId = $currentOption['record_id'];

            $currentOptionAttributes = [];
            $optionAttributes = $this->optionAttributes->getData();
            foreach ($optionAttributes as $optionAttribute) {
                $currentOptionAttributes[] = $optionAttribute->getName();
            }

            // set data to option $saved
            $savedOptionKey = $this->helper->searchArray('sort_order', $currentOptionSortOrder, $saved);
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

            $currentValues = isset($currentOption['values']) ? $currentOption['values'] : [];
            foreach ($currentValues as $currentValue) {
                $currentValueSortOrder = $currentValue['sort_order'];
                $currentValueRecordId = $currentValue['record_id'];

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
                $saved[$savedOptionKey]['values'][$savedValueKey]['record_id'] = $currentValueRecordId;
                $saved[$savedOptionKey]['values'][$savedValueKey]['mageworx_option_id'] =
                    $saved[$savedOptionKey]['mageworx_option_id'];

                foreach ($currentValueAttributes as $currentValueAttribute) {
                    if (!isset($currentValue[$currentValueAttribute])) {
                        continue;
                    }
                    $saved[$savedOptionKey]['values'][$savedValueKey][$currentValueAttribute] =
                        $currentValue[$currentValueAttribute];
                }
            }
        }

        return $saved;
    }
}
