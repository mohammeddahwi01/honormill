<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionLink\Helper;

use \MageWorx\OptionLink\Helper\Data as HelperData;
use \MageWorx\OptionBase\Model\Source\LinkedProductAttributes as LinkAttributesModel;
use \Magento\Framework\App\Helper\Context;

/**
 * OptionLink Attribute Helper.
 */
class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Map of attributes of a product which are replaced with the appropriate fields of options
     *
     * @var array
     */
    protected $fieldsMap = [
        'name' => [
            'option_name' => 'title',
            'type' => 'attribute',
            'joinType' => 'left',
        ],
        'price' => [
            'option_name' => 'price',
            'type' => 'attribute',
            'joinType' => 'left',
        ],
        'cost' => [
            'option_name' => 'cost',
            'type' => 'attribute',
            'joinType' => 'left',
        ],
        'weight' => [
            'option_name' => 'weight',
            'type' => 'attribute',
            'joinType' => 'left',
        ],
        'qty' => [
            'option_name' => 'qty',
            'type' => 'field',
            'alias' => 'qty',
            'table' => 'cataloginventory_stock_item',
            'field' => 'qty',
            'bind' => 'product_id=entity_id',
            'cond' => '{{table}}.stock_id=1',
            'joinType' => 'left',
        ]
    ];

    /**
     * @var \MageWorx\OptionLink\Helper\Data
     */
    protected $helperData;

    protected $linkAttributesModel;

    /**
     * Attribute constructor.
     *
     * @param Data $helperData
     * @param LinkAttributesModel $linkAttributesModel
     * @param Context $context
     */
    public function __construct(
        HelperData $helperData,
        LinkAttributesModel $linkAttributesModel,
        Context $context
    ) {
    
        $this->helperData = $helperData;
        $this->linkAttributesModel = $linkAttributesModel;
        parent::__construct($context);
    }

    /**
     * Check if product attribute selected for replace field of option
     *
     * @param $attribute
     * @return bool
     */
    public function isAttributeSelected($attribute)
    {
        return in_array($attribute, $this->getConvertedAttributesToFields());
    }

    /**
     * Retrieve selected product attributes like fields of option
     *
     * @return array
     */
    public function getConvertedAttributesToFields()
    {
        $attributes = $this->helperData->getLinkedProductAttributesAsArray();

        if (!$attributes) {
            return $attributes;
        }

        return $this->convertAttributesToFields($attributes);
    }

    /**
     * Retrieve ALL products attributes (used in "Link Assigned Product's Attributes" setting)
     * as option value fields
     *
     * @return array
     */
    public function getAllLinkAttributesAsFields()
    {
        $result = [];
        $attributes = $this->linkAttributesModel->toOptionArray();

        foreach ($attributes as $attribute) {
            $result[] = $attribute['value'];
        }

        return $this->convertAttributesToFields($result);
    }

    /**
     * Retrieve fields map array.
     * This array is used for:
     * - convert product attributes to option value fileds;
     * - join product attributes;
     *
     * @return array
     */
    public function getFieldsMap()
    {
        return $this->fieldsMap;
    }

    /**
     * This method converts product attributes
     * to option value fileds by fieldsMap.
     *
     * @param array $attributes
     * @return array
     */
    protected function convertAttributesToFields($attributes)
    {
        foreach ($this->getFieldsMap() as $key => $value) {
            $id = array_search($key, $attributes);
            if ($id !== false) {
                $attributes[$id] = $value['option_name'];
            }
        }

        return $attributes;
    }
}
