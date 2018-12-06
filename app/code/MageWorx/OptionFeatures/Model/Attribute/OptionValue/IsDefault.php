<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefault;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeIsDefault\Collection as IsDefaultCollection;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefaultFactory as IsDefaultFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class IsDefault extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var IsDefaultFactory
     */
    protected $isDefaultFactory;

    /**
     * @var IsDefaultCollection
     */
    protected $isDefaultCollection;

    /**
     * @param ResourceConnection $resource
     * @param IsDefaultFactory $isDefaultFactory
     * @param IsDefaultCollection $isDefaultCollection
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        IsDefaultFactory $isDefaultFactory,
        IsDefaultCollection $isDefaultCollection,
        Helper $helper,
        SystemHelper $systemHelper
    ) {
        $this->helper = $helper;
        $this->systemHelper = $systemHelper;
        $this->isDefaultFactory = $isDefaultFactory;
        $this->isDefaultCollection = $isDefaultCollection;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_IS_DEFAULT;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => OptionTypeIsDefault::TABLE_NAME,
            'group' => OptionTypeIsDefault::OPTIONTEMPLATES_TABLE_NAME
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }
        return $map[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, $options)
    {
        $this->entity = $entity;

        $isDefaults = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            if ($option['type'] == 'radio' || $option['type'] == 'drop_down') {
                $isDefaultValueAlreadySelected = false;
                foreach ($option['values'] as $value) {
                    if (!isset($value[$this->getName()])) {
                        continue;
                    }
                    if ($value[$this->getName()] == 1 && !$isDefaultValueAlreadySelected) {
                        $isDefaults[$value['mageworx_option_type_id']] = $value[$this->getName()];
                        $isDefaultValueAlreadySelected = true;
                    } else {
                        $isDefaults[$value['mageworx_option_type_id']] = 0;
                    }
                }
            } else {
                foreach ($option['values'] as $value) {
                    if (!isset($value[$this->getName()])) {
                        continue;
                    }
                    $isDefaults[$value['mageworx_option_type_id']] = $value[$this->getName()];
                }
            }
        }

        return $this->collectDefaults($isDefaults);
    }

    /**
     * Save defaults
     *
     * @param $items
     * @return array
     */
    protected function collectDefaults($items)
    {
        $storeId = $this->systemHelper->resolveCurrentStoreId();
        $data = [];
        foreach ($items as $itemKey => $itemValue) {

            $data['delete'][] = [
                OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID => $itemKey,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID => (int)$storeId,
            ];
            $data['save'][] = [
                OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID => $itemKey,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID => (int)$storeId,
                $this->getName() => $itemValue ? $itemValue : 0,
            ];
        }
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * Delete old option value defaults
     *
     * @param $data
     * @return void
     */
    public function deleteOldData($data)
    {
        $mageworxOptionValueIds = [];
        $storeId = 0;
        foreach ($data as $dataItem) {
            $mageworxOptionValueIds[] = $dataItem[OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID];
            $storeId = $dataItem[OptionTypeIsDefault::COLUMN_NAME_STORE_ID];
        }
        if (!$mageworxOptionValueIds) {
            return;
        }
        $tableName = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypeIsDefault::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID .
            " IN (" . "'" . implode("','", $mageworxOptionValueIds) . "'" . ") AND " .
            OptionTypeIsDefault::COLUMN_NAME_STORE_ID . " = '" . $storeId . "'";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }
}
