<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Entity;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use \Magento\Catalog\Model\Product\Option;
use \Magento\Catalog\Model\Product\Option\Value;
use \MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

class Base extends AbstractModel
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        OptionBaseHelper $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->optionAttributes = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->helper = $helper;
    }

    public function getBaseHelper()
    {
        return $this->helper;
    }

    /**
     * Convert product or group options to array and retrieve it.
     *
     * @param mixed $object
     * @return array
     */
    public function getOptionsAsArray($object)
    {
        $options = $object->getOptions();

        if ($options == null) {
            $options = [];
        }

        $showPrice = true;
        $results = [];

        foreach ($options as $option) {
            /* @var $option Option */
            $result = [];
            $result['id'] = $option->getOptionId();
            $result['item_count'] = $object->getItemCount();
            $result['option_id'] = $option->getOptionId();
            $result['mageworx_option_id'] = $this->getMageworxOptionId($option);
            $result['mageworx_group_option_id'] = $this->getGroupMageworxOptionId($option);
            $result['title'] = $option->getTitle();
            $result['type'] = $option->getType();
            $result['is_require'] = $option->getIsRequire();
            $result['sort_order'] = $option->getSortOrder();
            $result['can_edit_price'] = $object->getCanEditPrice();
            $result['group_option_id'] = $option->getGroupOptionId();
            if (!empty($object->getGroupId())) {
                $result['group_id'] = $object->getGroupId();
            }

            if ($option->getGroupByType() == Option::OPTION_GROUP_SELECT &&
                $option->getValues()
            ) {
                $itemCount = 0;
                foreach ($option->getValues() as $value) {
                    $i = $value->getOptionTypeId();
                    /* @var $value Value */
                    $result['values'][$i] = [
                        'item_count' => max($itemCount, $value->getOptionTypeId()),
                        'option_id' => $value->getOptionId(),
                        'option_type_id' => $value->getOptionTypeId(),
                        'mageworx_option_type_id' => $this->getMageworxOptionTypeId($value),
                        'mageworx_group_option_type_id' => $this->getGroupMageworxOptionTypeId($value),
                        'title' => $value->getTitle(),
                        'price' => $showPrice ?
                            $this->getPriceValue($value->getPrice(), $value->getPriceType()) :
                            0,
                        'price_type' => $showPrice && $value->getPriceType() ?
                            $value->getPriceType() :
                            'fixed',
                        'sku' => $value->getSku(),
                        'sort_order' => $value->getSortOrder(),
                        'group_option_value_id' => $value->getGroupOptionValueId(),
                    ];
                    if (!empty($object->getGroupId())) {
                        $result['values'][$i]['group_id'] = $object->getGroupId();
                    }
                    // Add option value attributes specified in the third-party modules to the option values
                    $result['values'][$i] = $this->addSpecificOptionValueAttributes($result['values'][$i], $value);
                }
            } else {
                $result['price'] = $showPrice ? $this->getPriceValue(
                    $option->getPrice(),
                    $option->getPriceType()
                ) : 0;
                $result['price_type'] = $option->getPriceType() ? $option->getPriceType() : 'fixed';
                $result['sku'] = $option->getSku();
                $result['max_characters'] = $option->getMaxCharacters();
                $result['file_extension'] = $option->getFileExtension();
                $result['image_size_x'] = $option->getImageSizeX();
                $result['image_size_y'] = $option->getImageSizeY();
                $result['values'] = null;
            }

            // Add option attributes specified in the third-party modules to the option
            $result = $this->addSpecificOptionAttributes($result, $option);
            $results[$option->getOptionId()] = $result;
        }

        return $results;
    }

    /**
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        if ($type == Value::TYPE_PERCENT) {
            $result = number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            $result = number_format($value, 2, null, '');
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Get mageworx_option_id from option
     *
     * @param $option
     * @return string
     */
    protected function getMageworxOptionId($option)
    {
        return $option->getMageworxOptionId();
    }

    /**
     * Get mageworx_option_type_id from option value
     *
     * @param $option
     * @return string
     */
    protected function getMageworxOptionTypeId($value)
    {
        return $value->getMageworxOptionTypeId();
    }

    /**
     * Get mageworx_option_id from option
     *
     * @param $option
     * @return string
     */
    protected function getGroupMageworxOptionId($option)
    {
        return $option->getMageworxOptionId();
    }

    /**
     * Get mageworx_option_type_id from option value
     *
     * @param $value
     * @return string
     */
    protected function getGroupMageworxOptionTypeId($value)
    {
        return $value->getMageworxOptionTypeId();
    }

    /**
     * Add specific third-party modules option attributes
     *
     * @param $result
     * @param $option
     * @return array
     */
    protected function addSpecificOptionAttributes($result, $option)
    {
        $attributes = $this->optionAttributes->getData();
        return $this->addSpecificAttributes($attributes, $result, $option);
    }

    /**
     * Add specific third-party modules option value attributes
     *
     * @param $result
     * @param $value
     * @return array
     */
    protected function addSpecificOptionValueAttributes($result, $value)
    {
        $attributes = $this->optionValueAttributes->getData();
        return $this->addSpecificAttributes($attributes, $result, $value);
    }

    /**
     * Add specific third-party modules attributes
     *
     * @param $attributes
     * @param $result
     * @param $object
     * @return array
     */
    protected function addSpecificAttributes($attributes, $result, $object)
    {
        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();
            $data = $object->getData();
            if (isset($data[$attributeName])) {
                $result[$attributeName] = $data[$attributeName];
            }
        }
        return $result;
    }
}
