<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionDescriptionFactory as DescriptionFactory;
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
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection,
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
            'product' => OptionDescription::TABLE_NAME,
            'group' => OptionDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isOptionDescriptionEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            if (!isset($option['description'])) {
                continue;
            }
            $descriptions[$option['mageworx_option_id']] = $option['description'];
        }

        return $this->collectDescriptions($descriptions);
    }

    /**
     * Save descriptions
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
                OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID => $itemKey,
                OptionDescription::COLUMN_NAME_STORE_ID => (int)$storeId,
            ];
            $data['save'][] = [
                OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID => $itemKey,
                OptionDescription::COLUMN_NAME_STORE_ID => (int)$storeId,
                OptionDescription::COLUMN_NAME_DESCRIPTION => $itemValue,
            ];
        }
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * Delete old option description
     *
     * @param $data
     * @return void
     */
    public function deleteOldData($data)
    {
        $mageworxOptionIds = [];
        $storeId = 0;
        foreach ($data as $dataItem) {
            $mageworxOptionIds[] = $dataItem[OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID];
            $storeId = $dataItem[OptionDescription::COLUMN_NAME_STORE_ID];
        }
        if (!$mageworxOptionIds) {
            return;
        }
        $tableName = $this->resource->getTableName($this->getTableName());
        $conditions = OptionDescription::COLUMN_NAME_MAGEWORX_OPTION_ID .
            " IN (" . "'" . implode("','", $mageworxOptionIds) . "'" . ") AND " .
            OptionDescription::COLUMN_NAME_STORE_ID . " = '" . $storeId . "'";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }
}
