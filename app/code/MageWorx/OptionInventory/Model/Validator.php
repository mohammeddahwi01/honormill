<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model;

/**
 * Validator model
 * @package MageWorx\OptionInventory\Model
 */
class Validator extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * Validator constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->stockHelper = $stockHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Validate Requested with Original data
     *
     * @param array $requestedData Requested Option Values
     * @param array $originData Original Option Values
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($requestedData, $originData)
    {
        foreach ($requestedData as $requestedValue) {
            $originValue = isset($originData[$requestedValue->getId()]) ? $originData[$requestedValue->getId()] : null;
            if (!$this->isAllow($requestedValue, $originValue)) {
                $this->addError($originValue);
            }
        }
    }

    /**
     * Check if allow original qty add requested qty
     *
     * @param \Magento\Framework\DataObject $requestedValue
     * @param \Magento\Catalog\Model\Product\Option\Value $originValue
     * @return bool
     */
    protected function isAllow($requestedValue, $originValue)
    {
        if (!$originValue) {
            return true;
        }

        if (!$originValue->getManageStock()) {
            return true;
        }

        if ($originValue->getQty() <= 0) {
            return false;
        }

        if ($requestedValue->getQty() > $originValue->getQty()) {
            return false;
        }

        return true;
    }

    /**
     * Throw exception
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addError($value)
    {
        $this->correctData($value);

        if ($value->getProductId()) {
            $formattedQty = $this->stockHelper->floatingQty($value->getQty(), $value->getProductId());
        } else {
            $formattedQty = $value->getQty();
        }
        $e = new \Magento\Framework\Exception\LocalizedException(
            __('We don\'t have as many  "%1" : "%2" - "%3"  as you requested (available qty: "%4").',
                $value->getProductName(),
                $value->getOptionTitle(),
                $value->getValueTitle(),
                $formattedQty));
        throw $e;
    }

    /**
     * Correct some option value fields.
     * For example: 'title' - can be origin or use product name linked by sku.
     *
     * SkuIsValid - this property set the OptionLink module.
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @return void
     */
    protected function correctData($value)
    {
        if ($value->getSkuIsValid()) {
            $valuesCollection = $this->objectManager
                ->create('\Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory')
                ->create();

            $valuesCollection
                ->addTitleToResult(1)
                ->getValuesByOption($value->getId());

            $item = $valuesCollection->getFirstItem();
            $value->setValueTitle($item->getTitle());
        }
    }

    /**
     * This function checks from where to take away quantity.
     *
     * @param array $value
     * @return string
     */
    public function getItemType($value)
    {
        $optionType = 'option';
        $productType = 'product';

        if (!isset($value['sku_is_valid'])) {
            return $optionType;
        }

        $skuIsValid = $value['sku_is_valid'];

        if ($skuIsValid) {
            return $productType;
        }

        return $optionType;
    }
}
