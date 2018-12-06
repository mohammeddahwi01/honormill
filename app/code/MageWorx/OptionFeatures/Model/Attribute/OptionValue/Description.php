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
use MageWorx\OptionFeatures\Model\OptionTypeDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionTypeDescriptionFactory as DescriptionFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Description extends AbstractAttribute implements AttributeInterface
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
     * @var DescriptionFactory
     */
    protected $descriptionFactory;

    /**
     * @var DescriptionCollection
     */
    protected $descriptionCollection;

    /**
     * @param ResourceConnection $resource
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection,
        Helper $helper,
        SystemHelper $systemHelper
    ) {
        $this->helper = $helper;
        $this->systemHelper = $systemHelper;
        $this->descriptionFactory = $descriptionFactory;
        $this->descriptionCollection = $descriptionCollection;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_DESCRIPTION;
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
            'product' => OptionTypeDescription::TABLE_NAME,
            'group' => OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isDescriptionEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value['description'])) {
                    continue;
                }
                $descriptions[$value['mageworx_option_type_id']] = $value['description'];
            }
        }

        return $this->collectDescriptions($descriptions);
    }

    /**
     * Collect descriptions
     *
     * @param $items
     * @return array
     */
    protected function collectDescriptions($items)
    {
        $storeId = $this->systemHelper->resolveCurrentStoreId();
        $data = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID => $itemKey,
                OptionTypeDescription::COLUMN_NAME_STORE_ID => (int)$storeId,
            ];
            $data['save'][] = [
                OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID => $itemKey,
                OptionTypeDescription::COLUMN_NAME_STORE_ID => (int)$storeId,
                OptionTypeDescription::COLUMN_NAME_DESCRIPTION => $itemValue
            ];
        }
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * Delete old option value description
     *
     * @param $data
     * @return void
     */
    public function deleteOldData($data)
    {
        $mageworxOptionValueIds = [];
        $storeId = 0;
        foreach ($data as $dataItem) {
            $mageworxOptionValueIds[] = $dataItem[OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID];
            $storeId = $dataItem[OptionTypeDescription::COLUMN_NAME_STORE_ID];
        }
        if (!$mageworxOptionValueIds) {
            return;
        }
        $tableName = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypeDescription::COLUMN_NAME_MAGEWORX_OPTION_TYPE_ID .
            " IN (" . "'" . implode("','", $mageworxOptionValueIds) . "'" . ") AND " .
            OptionTypeDescription::COLUMN_NAME_STORE_ID . " = '" . $storeId . "'";
        $this->resource->getConnection()->delete($tableName,$conditions);
    }
}
