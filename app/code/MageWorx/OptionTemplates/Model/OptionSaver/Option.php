<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\OptionSaver;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use MageWorx\OptionTemplates\Model\OptionSaver\Value as OptionValueDataCollector;

class Option extends \Magento\Catalog\Model\ResourceModel\Product\Option
{
    const OPTION_TYPE_FIELD     = 'field';
    const OPTION_TYPE_AREA      = 'area';
    const OPTION_TYPE_FILE      = 'file';
    const OPTION_TYPE_DATE      = 'date';
    const OPTION_TYPE_DATE_TIME = 'date_time';
    const OPTION_TYPE_TIME      = 'time';

    const TABLE_NAME_CATALOG_PRODUCT_OPTION       = 'catalog_product_option';
    const TABLE_NAME_CATALOG_PRODUCT_OPTION_PRICE = 'catalog_product_option_price';
    const TABLE_NAME_CATALOG_PRODUCT_OPTION_TITLE = 'catalog_product_option_title';

    /**
     * @var OptionValueDataCollector
     */
    protected $optionValueDataCollector;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param OptionValueDataCollector $optionValueDataCollector
     * @param OptionRepository $optionRepository
     * @param OptionAttributes $optionAttributes
     * @param BaseHelper $baseHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        OptionValueDataCollector $optionValueDataCollector,
        OptionRepository $optionRepository,
        OptionAttributes $optionAttributes,
        BaseHelper $baseHelper,
        $connectionName = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->baseHelper = $baseHelper;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueDataCollector = $optionValueDataCollector;
        parent::__construct($context, $currencyFactory, $storeManager, $config, $connectionName);
    }

    /**
     * Collect option data before multiple insert
     * Used to increase performance for applying option template to great amount of products
     *
     * @param ProductInterface $product
     * @param array $optionData
     * @param array $currentIncrementIds
     * @return void
     */
    public function collectOptionsBeforeInsert(
        ProductInterface $product,
        &$optionData,
        &$currentIncrementIds,
        &$optionsToDelete
    ) {
        foreach ($this->optionRepository->getProductOptions($product) as $option) {
            $optionsToDelete[] = $option->getOptionId();
        }

        if (!$product->getOptions()) {
            return;
        }

        foreach ($product->getOptions() as $option) {
            if ($option->getData('is_delete') == true) {
                continue;
            }
            //compatibility for 2.2.x
            if ($this->baseHelper->checkModuleVersion('101.0.10')) {
                $option->setOptionId(null);
                if (!empty($option['values'])) {
                    $values = [];
                    foreach ($option['values'] as $valueKey => $value) {
                        $value['option_type_id'] = null;
                        $values[$valueKey] = $value;
                    }
                    $option->setValues($values);
                }
                if (!empty($option->getData('values'))) {
                    $values = [];
                    foreach ($option->getData('values') as $valueKey => $value) {
                        $value['option_type_id'] = null;
                        $values[$valueKey] = $value;
                    }
                    $option->setData('values', $values);
                }
            }

            $this->collectOptionData($product, $option, $optionData, $currentIncrementIds);
        }

        return;
    }

    /**
     * Collect option's data
     *
     * @param ProductInterface $product
     * @param ProductCustomOptionInterface $option
     * @param array $optionData
     * @param array $currentIncrementIds
     * @return void
     */
    protected function collectOptionData($product, &$option, &$optionData, &$currentIncrementIds)
    {
        $newOptionId = $currentIncrementIds['option'];
        $currentIncrementIds['option'] += 1;
        $option->setOptionId($newOptionId);
        if (empty($option->getMageworxOptionId())) {
            $option->setMageworxOptionId($this->baseHelper->generateUUIDv4());
        }
        $option['group_option_id'] = $option->getData('group_option_id');

        $data = [
            'option_id' => $option->getData('option_id'),
            'product_id' => $product->getData($product->getResource()->getLinkField()),
            'mageworx_option_id' => $option->getData('mageworx_option_id'),
            'group_option_id' => $option->getData('group_option_id'),
            'type' => $option->getData('type'),
            'is_require' => $option->getData('is_require'),
            'sku' => $option->getData('sku'),
            'max_characters' => $option->getData('max_characters'),
            'file_extension' => $option->getData('file_extension'),
            'image_size_x' => $option->getData('image_size_x'),
            'image_size_y' => $option->getData('image_size_y'),
            'sort_order' => $option->getData('sort_order')
        ];

        foreach ($this->optionAttributes->getData() as $attribute) {
            if (!$attribute->hasOwnTable()) {
                $data[$attribute->getName()] = $attribute->prepareDataBeforeSave($option);
            }
        }

        $catalogProductOptionTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_OPTION);
        $optionData[self::TABLE_NAME_CATALOG_PRODUCT_OPTION][$option->getOptionId()] = $this->_prepareDataForTable(
            new \Magento\Framework\DataObject($data),
            $catalogProductOptionTable
        );

        $this->collectPriceData($option, $optionData);
        $this->collectTitleData($option, $optionData);

        if (!empty($option->getValues())) {
            $this->optionValueDataCollector->collectValuesBeforeInsert($option, $optionData, $currentIncrementIds);
        }
    }

    /**
     * Collect option prices for non-selectable options before multiple insert
     *
     * @param ProductCustomOptionInterface $option
     * @param array $optionData
     * @return void
     */
    protected function collectPriceData($option, &$optionData)
    {
        $optionPriceTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_OPTION_PRICE);

        /*
         * Better to check param 'price' and 'price_type' for saving.
         * If there is not price skip saving price
         */
        if (!in_array($option->getType(), $this->getNonSelectableTypes())) {
            return;
        }

        //save for store_id = 0
        if (!$option->getData('scope', 'price')) {
            $data = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject(
                    [
                        'option_id' => $option->getOptionId(),
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        'price' => $option->getPrice(),
                        'price_type' => $option->getPriceType(),
                    ]
                ),
                $optionPriceTable
            );
            $optionData[self::TABLE_NAME_CATALOG_PRODUCT_OPTION_PRICE][$option->getOptionId()] = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($data),
                $optionPriceTable
            );
        }
    }

    /**
     * Collect option titles for non-selectable options before multiple insert
     *
     * @param ProductCustomOptionInterface $option
     * @param array $optionData
     * @return void
     */
    protected function collectTitleData($option, &$optionData)
    {
        $optionTitleTableName = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_OPTION_TITLE);
        $storeIds = [];
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        foreach ($storeIds as $storeId) {
            if (!$option->getTitle()) {
                return;
            }

            $data = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject(
                    [
                        'option_id' => $option->getOptionId(),
                        'store_id' => $storeId,
                        'title' => $option->getTitle(),
                    ]
                ),
                $optionTitleTableName
            );

            $optionData[self::TABLE_NAME_CATALOG_PRODUCT_OPTION_TITLE][$option->getOptionId()] =
                $this->_prepareDataForTable(
                    new \Magento\Framework\DataObject($data),
                    $optionTitleTableName
                );
        }
    }

    /**
     * All Option Types that support price and price_type
     *
     * @return string[]
     */
    public function getNonSelectableTypes()
    {
        return [
            self::OPTION_TYPE_FIELD,
            self::OPTION_TYPE_AREA,
            self::OPTION_TYPE_FILE,
            self::OPTION_TYPE_DATE,
            self::OPTION_TYPE_DATE_TIME,
            self::OPTION_TYPE_TIME
        ];
    }
}