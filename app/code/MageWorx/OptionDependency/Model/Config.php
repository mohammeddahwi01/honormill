<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Config extends AbstractExtensibleModel
{
    const OPTION_TYPE_DROP_DOWN = 'drop_down';
    const OPTION_TYPE_RADIO     = 'radio';
    const OPTION_TYPE_CHECKBOX  = 'checkbox';
    const OPTION_TYPE_MULTIPLE  = 'multiple';

    const TABLE_NAME                 = 'mageworx_option_dependency';
    const OPTIONTEMPLATES_TABLE_NAME = 'mageworx_optiontemplates_group_option_dependency';

    const COLUMN_NAME_DEPENDENCY_ID          = 'dependency_id';
    const COLUMN_NAME_CHILD_OPTION_ID        = 'child_option_id';
    const COLUMN_NAME_CHILD_OPTION_TYPE_ID   = 'child_option_type_id';
    const COLUMN_NAME_PARENT_OPTION_ID       = 'parent_option_id';
    const COLUMN_NAME_PARENT_OPTION_TYPE_ID  = 'parent_option_type_id';
    const COLUMN_NAME_PRODUCT_ID             = 'product_id';
    const COLUMN_NAME_GROUP_ID               = 'group_id';
    const COLUMN_NAME_OPTION_TYPE_TITLE_ID   = 'option_type_title_id';
    const COLUMN_NAME_OPTION_TITLE_ID        = 'option_title_id';
    const COLUMN_NAME_OPTION_DEPENDENCY_TYPE = 'dependency_type';

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\OptionDependency\Model\ResourceModel\Config');
        $this->setIdFieldName('dependency_id');
    }

    /**
     * Get product options
     *
     * @param integer $productId
     * @return array
     */
    public function allProductOptions($productId)
    {
        return $data = $this->_getResource()->allProductOptions($productId);
    }

    /**
     * Get 'child_option_id' - 'parent_option_type_id' pairs
     *
     * @param integer $productId
     * @return array
     */
    public function getOptionParents($productId)
    {
        $columns = ['child_option_id', 'parent_option_type_id'];
        $data    = $this->_getResource()
                        ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'child_option_type_id' - 'parent_option_type_id' pairs in json
     *
     * @param integer $productId
     * @return array
     */
    public function getValueParents($productId)
    {
        $columns = ['child_option_type_id', 'parent_option_type_id'];
        $data    = $this->_getResource()
                        ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'parent_option_type_id' - 'child_option_id' pairs in json
     *
     * @param integer $productId
     * @return array
     */
    public function getOptionChildren($productId)
    {
        $columns = ['parent_option_type_id', 'child_option_id'];
        $data    = $this->_getResource()
                        ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get 'parent_option_type_id' - 'child_option_type_id' pairs in json
     *
     * @param integer $productId
     * @return array
     */
    public function getValueChildren($productId)
    {
        $columns = ['parent_option_type_id', 'child_option_type_id'];
        $data    = $this->_getResource()
                        ->loadDependencies($productId, $columns);

        return $this->compactArray($data, $columns);
    }

    /**
     * Get option types ('mageworx_option_id' => 'type') in json
     *
     * @param integer $productId
     * @return array
     */
    public function getOptionTypes($productId)
    {
        $data = $this->_getResource()
                     ->loadOptionTypes($productId);

        return $data;
    }

    /**
     * Get options with AND-dependency type
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getAndDependencyOptions($product)
    {
        $config = [];
        /** @var \Magento\Catalog\Model\Product\Option[] $options */
        $options = $product->getOptions();
        foreach ($options as $option) {
            if ($option->getDependencyType()) {
                $config[$option->getData('mageworx_option_id')] = (bool)$option->getDependencyType();
            }
            if (empty($option->getValues())) {
                continue;
            }
            /** @var \Magento\Catalog\Model\Product\Option\Value $value */
            foreach ($option->getValues() as $value) {
                if (is_array($value)) {
                    if (empty($value['mageworx_option_type_id'])) {
                        continue;
                    }
                    $config[$value['mageworx_option_type_id']] = (bool)$value['dependency_type'];
                } elseif ($value->getDependencyType()) {
                    $config[$value->getData('mageworx_option_type_id')] = (bool)$value->getDependencyType();
                }
            }
        }

        return $config;
    }

    /**
     * Retrieve array of mageworx_option_id (mageworx_option_type_id) by option_id (option_type_id)
     *
     * @param string $code
     * @param array $ids
     * @return array
     */
    public function convertToMageworxId($code = 'option', $ids = [])
    {
        $resource = $this->_getResource();

        switch ($code) {
            case 'option':
                $data = $resource->loadMageworxOptionId($ids);
                break;
            case 'value':
                $data = $resource->loadMageworxOptionTypeId($ids);
                break;
        }

        return $data;
    }

    /**
     * Compact array, remove duplicates
     *
     * @param array $array
     * @param array $cols
     * @return array
     */
    protected function compactArray($array, $cols)
    {
        $keyName   = $cols[0];
        $valueName = $cols[1];

        $result = [];

        foreach ($array as $row) {
            $key   = $row[$keyName];
            $value = $row[$valueName];

            if (!isset($result[$key])) {
                $result[$key][] = $value;
                continue;
            }

            if (in_array($value, $result[$key])) {
                continue;
            }

            $result[$key][] = $value;
        }

        return $result;
    }

    /**
     * Check if it is needed to validate dependent option
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param array $frontOptions
     * @param ProductInterface $product
     * @param integer $productId
     * @return bool
     */
    public function isNeedDependentOptionValidation($option, $frontOptions, $product, $productId)
    {
        $allProductOptions    = $this->allProductOptions($productId);
        $selectedValues       = $this->convertToMageworxId('value', $this->getSelectedValues($frontOptions));
        $optionParents        = $this->getOptionParents($productId);
        $valueParents         = $this->getValueParents($productId);
        $andDependencyOptions = $this->getAndDependencyOptions($product);
        $optionMageworxId     = $allProductOptions[$option->getId()];

        if (is_null($option->getValues())) {
            return false;
        }

        // 1. If object not exist in parentDependencies then it is not dependent
        // and return true.
        if (!in_array($optionMageworxId, array_keys($optionParents))) {
            return true;
        }

        $isDisabledData = $this->prepareIsDisabledData($valueParents);

        // 2. OR dependency: if any of parents are selected - return true
        // AND dependency: if all of parents are selected - return true
        $parentSelected = true;
        if (!empty($option->getValues())) {
            $mageworxOptionTypeIds = [];
            foreach ($option->getValues() as $optionValue) {
                if (is_array($optionValue)) {
                    if (!empty($value['mageworx_option_type_id'])) {
                        $mageworxOptionTypeIds[] = $value['mageworx_option_type_id'];
                    }
                } else {
                    $mageworxOptionTypeIds[] = $optionValue->getMageworxOptionTypeId();
                }
            }

            $parentSelected       = false;
            $disableRequireOption = false;
            foreach ($valueParents as $childValueId => $parentValueIds) {
                if (!in_array($childValueId, $mageworxOptionTypeIds)) {
                    continue;
                }

                if (in_array($childValueId, array_keys($andDependencyOptions))) {
                    $parentSelected = true;
                    foreach ($parentValueIds as $parentValueId) {
                        if (!in_array($parentValueId, $selectedValues)) {
                            $parentSelected = false;
                            break;
                        }

                        if ($this->isDisabledParentOption($isDisabledData, $parentValueId)) {
                            $disableRequireOption = true;
                        }
                    }
                } else {
                    foreach ($parentValueIds as $parentValueId) {
                        if (in_array($parentValueId, $selectedValues)) {
                            $parentSelected = true;

                            if ($this->isDisabledParentOption($isDisabledData, $parentValueId)) {
                                $disableRequireOption = true;
                            }
                            break;
                        }
                    }
                }
                if ($parentSelected) {
                    if ($disableRequireOption) {
                        return false;
                    }

                    return true;
                }
            }
        } elseif (!$this->isSelectableOptionType($option->getType())) {
            $parentSelected = false;
            $parents        = $optionParents[$optionMageworxId];
            if (in_array($optionMageworxId, array_keys($andDependencyOptions))) {
                $parentSelected = true;
                foreach ($parents as $parentValueId) {
                    if (!in_array($parentValueId, $selectedValues)) {
                        $parentSelected = false;
                        break;
                    }
                }
            } else {
                foreach ($parents as $parentValueId) {
                    if (in_array($parentValueId, $selectedValues)) {
                        $parentSelected = true;
                        break;
                    }
                }
            }
        }

        // if option is required and hidden (parent value not selected) - set IsRequire = false
        if (!$parentSelected) {
            return false;
        }

        return true;
    }

    /**
     * @param array $valueParents
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareIsDisabledData($valueParents)
    {
        $ids = [];

        foreach ($valueParents as $childValueId => $parentValueIds) {
            foreach ($parentValueIds as $parentValueId) {
                $ids[] = $parentValueId;
            }
        }

        $ids      = array_unique($ids);
        $resource = $this->_getResource();
        $data     = $resource->getIsDisabledData($ids);

        return $data;
    }

    /**
     *
     * @param array $prepareData
     * @param int $parentValueId
     * @return bool
     */
    protected function isDisabledParentOption($prepareData, $parentValueId)
    {
        foreach ($prepareData as $value) {
            if ($value['parent_option_type_id'] == $parentValueId) {
                return (bool)$value['disabled'];
            }
        }

        return false;
    }

    /**
     * Get selected values
     *
     * @param array|null $frontOptions
     * @return array
     */
    protected function getSelectedValues($frontOptions)
    {
        $result = [];

        if (!is_array($frontOptions) || !$frontOptions) {
            return $result;
        }

        foreach ($frontOptions as $optionId => $values) {
            if (!is_array($values) && !is_numeric($values)) {
                continue;
            }

            if (is_numeric($values)) {
                $values = [$values];
            }

            $result = array_merge($result, $values);
        }

        return $result;
    }

    /**
     * Check if option has selectable type
     *
     * @param string $optionType
     * @return bool
     */
    public function isSelectableOptionType($optionType)
    {
        if ($optionType == self::OPTION_TYPE_CHECKBOX
            || $optionType == self::OPTION_TYPE_DROP_DOWN
            || $optionType == self::OPTION_TYPE_RADIO
            || $optionType == self::OPTION_TYPE_MULTIPLE
        ) {
            return true;
        }

        return false;
    }
}
