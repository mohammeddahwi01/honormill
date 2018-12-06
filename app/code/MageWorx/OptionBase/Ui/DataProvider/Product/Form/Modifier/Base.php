<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\App\Request\Http;

class Base extends AbstractModifier implements ModifierInterface
{
    const FIELD_SORT_ORDER_NAME = 'sort_order';

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var string
     */
    protected $form = 'product_form';

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->request->getRouteName() == 'mageworx_optiontemplates') {
            $this->form = 'mageworx_optiontemplates_group_form';
        }

        $this->addSortOrderField();

        return $this->meta;
    }

    /**
     * Adds sort order field to the meta-data
     */
    protected function addSortOrderField()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        // Add fields to the values
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $this->getValueFieldsConfig()
        );

        // Add fields to the option
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $this->getOptionFieldsConfig()
        );
    }

    /**
     * The custom option fields config
     *
     * @return array
     */
    protected function getOptionFieldsConfig()
    {
        $fields[self::FIELD_SORT_ORDER_NAME] = $this->getSortOrderConfig(40);

        return $fields;
    }

    /**
     * The custom option value fields config
     *
     * @return array
     */
    protected function getValueFieldsConfig()
    {
        $fields[self::FIELD_SORT_ORDER_NAME] = $this->getSortOrderConfig(50);

        return $fields;
    }

    /**
     * Is default field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getSortOrderConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Sort Order'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => true,
                        'validation' => [
                            'validate-number' => true,
                            'validate-zero-or-greater' => true,
                            'required-entry' => true
                        ],
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return false;
    }
}
