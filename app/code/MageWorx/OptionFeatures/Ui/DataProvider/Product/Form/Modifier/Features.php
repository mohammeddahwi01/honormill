<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;

class Features extends AbstractModifier implements ModifierInterface
{
    const OPTION_VALUE_IMAGES_LAYOUT         = 'option_value_image_index';
    const OPTION_VALUE_IMAGES_MODAL_INDEX    = 'option_value_images_modal';
    const OPTION_VALUE_IMAGES_FORM           = 'option_value_images_form';
    const OPTION_VALUE_IMAGES_CONTENT        = 'content';
    const OPTION_VALUE_IMAGES_MODAL_FIELDSET = 'fieldset';
    const CONTAINER_HEADER_NAME              = 'header';
    const KEY_OPTION_GALLERY_DISPLAY_MODE    = 'mageworx_option_gallery';
    const KEY_OPTION_IMAGE_MODE              = 'mageworx_option_image_mode';
    const OPTION_IMAGE_MODE_DISABLED         = 0;
    const OPTION_IMAGE_MODE_REPLACE          = 1;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var Helper
     */
    protected $helper;

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
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param LocatorInterface $locator
     * @param Helper $helper
     * @param Http $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        LocatorInterface $locator,
        Helper $helper,
        Http $request,
        UrlInterface $urlBuilder
    ) {
        $this->arrayManager = $arrayManager;
        $this->storeManager = $storeManager;
        $this->locator      = $locator;
        $this->helper       = $helper;
        $this->request      = $request;
        $this->urlBuilder   = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        if (!$product || !$product->getId()) {
            return $data;
        }

        return array_replace_recursive(
            $data,
            [
                $product->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        Helper::KEY_ABSOLUTE_COST   => $product->getData(Helper::KEY_ABSOLUTE_COST),
                        Helper::KEY_ABSOLUTE_WEIGHT => $product->getData(Helper::KEY_ABSOLUTE_WEIGHT),
                        Helper::KEY_ABSOLUTE_PRICE  => $product->getData(Helper::KEY_ABSOLUTE_PRICE),
                    ],
                ],
            ]
        );
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

        $this->addFeaturesFields();

        $this->addImageModal();
        $this->addImagesButton();
        $this->addImageModeConfig();

        return $this->meta;
    }

    /**
     * Adds features fields to the meta-data
     */
    protected function addFeaturesFields()
    {
        $groupCustomOptionsName    = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName       = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        // Add fields to the values
        $valueFeaturesFields                                                           = $this->getValueFeaturesFieldsConfig(
        );
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $valueFeaturesFields
        );

        // Add fields to the option
        $optionFeaturesFields                                                      = $this->getOptionFeaturesFieldsConfig(
        );
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $optionFeaturesFields
        );

        // Add fields to the options container
        $productFeaturesFields                           = $this->getProductFeaturesFieldsConfig();
        $this->meta[$groupCustomOptionsName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children'],
            $productFeaturesFields
        );
    }

    /**
     * The custom option value fields config
     *
     * @return array
     */
    protected function getValueFeaturesFieldsConfig()
    {
        $fields = [];
        if ($this->helper->isCostEnabled()) {
            $fields[Helper::KEY_COST] = $this->getCostConfig(130);
        }
        if ($this->helper->isWeightEnabled()) {
            $fields[Helper::KEY_WEIGHT] = $this->getWeightConfig(140);
        }
        if ($this->helper->isDescriptionEnabled()) {
            $fields[Helper::KEY_DESCRIPTION] = $this->getDescriptionConfig(145);
        }

        if ($this->helper->isDefaultEnabled()) {
            $fields[Helper::KEY_IS_DEFAULT] = $this->getIsDefaultConfig(146);
        }

        if ($this->helper->isEnabledIsDisabled()) {
            $fields[Helper::KEY_DISABLED] = $this->getIsDisabledConfig(147);
        }        

        if ($this->helper->isStocktabEnabled()) {
            $fields[Helper::KEY_IS_STOCKTAB] = $this->getIsStocktabConfig(148);
        }

        if ($this->helper->isCustomtabEnabled()) {
            $fields[Helper::KEY_IS_CUSTOMTAB] = $this->getIsCustomtabConfig(149);
        }

        return $fields;
    }

    protected function getIsStocktabConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('FastShip'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_IS_STOCKTAB,
                        'dataType' => Text::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }
    protected function getIsCustomtabConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('CustomOrder'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_IS_CUSTOMTAB,
                        'dataType' => Text::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Is default field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getIsDefaultConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Is Default'),
                        'componentType' => Field::NAME,
                        'component'     => 'MageWorx_OptionFeatures/js/element/option-type-dependent-checkbox',
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_IS_DEFAULT,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::IS_DEFAULT_TRUE,
                            'false' => Helper::IS_DEFAULT_FALSE,
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Is disabled value field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getIsDisabledConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Disabled'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_DISABLED,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::DISABLED_TRUE,
                            'false' => Helper::DISABLED_FALSE,
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Cost field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getCostConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Cost'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => Helper::KEY_COST,
                        'dataType'      => Number::NAME,
                        'addbefore'     => $this->getBaseCurrencySymbol(),
                        'validation'    => [
                            'validate-number'          => true,
                            'validate-zero-or-greater' => true,
                        ],
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Weight field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getWeightConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'             => __('Weight'),
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'dataScope'         => Helper::KEY_WEIGHT,
                        'dataType'          => Number::NAME,
                        'validation'        => [
                            'validate-number'          => true,
                            'validate-zero-or-greater' => true,
                        ],
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'admin__field-small',
                        'addafter'          => $this->getWeightUnit(),
                        'imports'           => [
                            'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                                . '.product_has_weight:value',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Description field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getDescriptionConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Description'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => Helper::KEY_DESCRIPTION,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * The custom option fields config
     *
     * @return array
     */
    protected function getOptionFeaturesFieldsConfig()
    {
        $fields = [];

        if ($this->helper->isOneTimeEnabled()) {
            $fields[Helper::KEY_ONE_TIME] = $this->getOneTimeConfig(60);
        }

        if ($this->helper->isQtyInputEnabled()) {
            $fields[Helper::KEY_QTY_INPUT] = $this->getQtyInputConfig(65);
        }

        if ($this->helper->isEnabledIsDisabled()) {
            $fields[Helper::KEY_DISABLED] = $this->getOptionIsDisabledConfig(74);
        }

        if ($this->helper->isOptionDescriptionEnabled()) {
            $fields[Helper::KEY_OPTION_DESCRIPTION] = $this->getOptionDescriptionInputConfig(75);
        }

        if ($this->helper->isAllOptionsIncludeEnabled()) {
            $fields['all_options_include'] = $this->getAllOptionsInclude(69);
            $fields['all_options_includeco'] = $this->getAllOptionsIncludeCO(71);
        }

        $fields['is_fast_ship'] = $this->getIsFastShip(70);
        $fields['is_custom_order'] = $this->getIsCustomOrder(72);

        return $fields;
    }

    /**
     * Is disabled option field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getOptionIsDisabledConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Disabled'),
                        'componentType' => Field::NAME,
                        'formElement'   => Hidden::NAME,
                        'dataScope'     => Helper::KEY_DISABLED,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::DISABLED_TRUE,
                            'false' => Helper::DISABLED_FALSE,
                        ],

                        'fit'           => true,
                        'visible'       => false,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Option Description field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getOptionDescriptionInputConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Description'),
                        'componentType' => Field::NAME,
                        'formElement'   => Textarea::NAME,
                        'dataScope'     => Helper::KEY_OPTION_DESCRIPTION,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * Enable qty input (for option) field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getQtyInputConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Qty Input'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_QTY_INPUT,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'valueMap'      => [
                            'true'  => Helper::QTY_INPUT_TRUE,
                            'false' => Helper::QTY_INPUT_FALSE,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Is One Time Option field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getOneTimeConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('One Time'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_ONE_TIME,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'prefer'        => 'toggle',
                        'tooltip'       => [
                            'description' => __(
                                    'Due to Magento calculations, the one-time option price is divided by the added quantity to get the final price.'
                                ) .
                                ' ' .
                                __('Note: the final price may possibly differ by 1-2 cents.')
                        ],
                        'valueMap'      => [
                            'true'  => Helper::ONE_TIME_TRUE,
                            'false' => Helper::ONE_TIME_FALSE,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getIsCustomOrder($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-customoder',
                        'label' => __('Custom Order ON/OFF'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'is_custom_order',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'tooltip' => [
                            'description' => __('If “CustomOrder Include All” option is not checked, then “CustomOrder ON/OFF” will affected for new assigned products.')
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function getIsFastShip($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-fastship',
                        'label' => __('Fast Ship ON/OFF'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'is_fast_ship',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'tooltip' => [
                            'description' => __('If “FastShip Include All” option is not checked, then “FastShip ON/OFF” will affected for new assigned products.')
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function getAllOptionsInclude($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-alloptincl',
                        'label' => __('FastShip Include All'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'all_options_include',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'tooltip' => [
                            'description' => __('“FastShip Include All” option is only decide FastShip options ON/OFF based on selection of “FastShip ON/OFF”. If  “FastShip Include All” option checked then It will affected for all products.')
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function getAllOptionsIncludeCO($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-alloptinclco',
                        'label' => __('CustomOrder Include All'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'all_options_includeco',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                        'tooltip' => [
                            'description' => __('“CustomOrder Include All” option is only decide FastShip options ON/OFF based on selection of “CustomOrder ON/OFF”. If  “CustomOrder Include All” option checked then It will affected for all products.')
                        ]
                    ],
                ],
            ],
        ];
    }

    /**
     * The product based features config (see `catalog_product_entity` table)
     *
     * @return array
     */
    protected function getProductFeaturesFieldsConfig()
    {

        $children = [];
        if ($this->helper->isAbsoluteCostEnabled()) {
            $children[Helper::KEY_ABSOLUTE_COST] = $this->getAbsoluteCostConfig(5);
        }
        if ($this->helper->isAbsoluteWeightEnabled()) {
            $children[Helper::KEY_ABSOLUTE_WEIGHT] = $this->getAbsoluteWeightConfig(7);
        }
        if ($this->helper->isAbsolutePriceEnabled()) {
            $children[Helper::KEY_ABSOLUTE_PRICE] = $this->getAbsolutePriceConfig(9);
        }

        $fields = [
            'global_config_container' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType'     => Container::NAME,
                            'formElement'       => Container::NAME,
                            'component'         => 'Magento_Ui/js/form/components/group',
                            'breakLine'         => false,
                            'showLabel'         => false,
                            'additionalClasses' =>
                                'admin__field-control admin__control-grouped admin__field-group-columns',
                            'sortOrder'         => 10,
                        ],
                    ],
                ],
                'children'  => $children
            ],
        ];

        return $fields;
    }

    /**
     * Absolute Cost field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsoluteCostConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Absolute Cost'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_ABSOLUTE_COST,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::ABSOLUTE_COST_TRUE,
                            'false' => Helper::ABSOLUTE_COST_FALSE,
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Absolute Weight field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsoluteWeightConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Absolute Weight'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_ABSOLUTE_WEIGHT,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::ABSOLUTE_WEIGHT_TRUE,
                            'false' => Helper::ABSOLUTE_WEIGHT_FALSE,
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Absolute Price field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsolutePriceConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Absolute Price'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => Helper::KEY_ABSOLUTE_PRICE,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => Helper::ABSOLUTE_PRICE_TRUE,
                            'false' => Helper::ABSOLUTE_PRICE_FALSE,
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getBaseCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * Get weight unit name
     *
     * @return mixed
     */
    protected function getWeightUnit()
    {
        try {
            $unit = $this->locator->getStore()->getConfig('general/locale/weight_unit');
        } catch (\Exception $e) {
            $unit = $this->storeManager->getStore()->getConfig('general/locale/weight_unit');
        }

        return $unit;
    }

    protected function addImageModal()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::OPTION_VALUE_IMAGES_MODAL_INDEX => $this->getModalConfig(),
            ]
        );
    }

    protected function getModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'isTemplate'    => false,
                        'componentType' => Modal::NAME,
                        'provider'      => static::FORM_NAME . '.' . static::FORM_NAME . '_data_source',
                        'options'       => [
                            'title' => __('Manage Images'),
                        ],
                        'imports'       => [
                            'state' => '!index=' . static::OPTION_VALUE_IMAGES_CONTENT . ':responseStatus',
                        ],
                    ],
                ],
            ],
            'children'  => [
                static::OPTION_VALUE_IMAGES_CONTENT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender'       => false,
                                'label'            => '',
                                'componentType'    => 'container',
                                'component'        => 'MageWorx_OptionFeatures/js/components/images-insert-form',
                                'update_url'       => $this->urlBuilder->getUrl('mui/index/render'),
                                'render_url'       => $this->urlBuilder->getUrl(
                                    'mui/index/render_handle',
                                    [
                                        'handle'   => static::OPTION_VALUE_IMAGES_LAYOUT,
                                        'store'    => $this->locator->getProduct()->getStoreId(),
                                        'buttons'  => 1,
                                        'formName' => $this->form,
                                    ]
                                ),
                                'ns'               => static::OPTION_VALUE_IMAGES_FORM,
                                'externalProvider' => static::OPTION_VALUE_IMAGES_FORM . '.'
                                    . static::OPTION_VALUE_IMAGES_FORM . '_data_source',
                                'toolbarContainer' => '${ $.parentName }',
                                'formSubmitType'   => 'ajax',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function addImagesButton()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName    = CustomOptions::CONTAINER_OPTION;

        $imagesButtonFieldConfig                                                       = $this->getImagesButtonFieldConfig(
        );
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $imagesButtonFieldConfig
        );

        $imagesHiddenFieldConfig                                                       = $this->getImagesHiddenFieldConfig(
        );
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $imagesHiddenFieldConfig
        );
    }

    /**
     * The custom option value fields config
     *
     * @return array
     */
    protected function getImagesButtonFieldConfig()
    {
        $fields                = [];
        $fields['images_link'] = $this->getImagesButtonConfig(150);

        return $fields;
    }

    /**
     * Upload image field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getImagesButtonConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
//                        'label' => __('Images'),
                        'displayAsLink' => false,
                        'formElement'   => Container::NAME,
                        'componentType' => Container::NAME,
                        'component'     => 'MageWorx_OptionFeatures/js/components/button',
                        'actions'       => [
                            [
                                'targetName' => 'ns=' . $this->form . ', index='
                                    . static::OPTION_VALUE_IMAGES_MODAL_INDEX, // selector
                                'actionName' => 'openModal',
                            ],
                            [
                                'targetName' =>
                                    $this->form . '.' . $this->form . '.'
                                    . static::OPTION_VALUE_IMAGES_MODAL_INDEX . '.' . static::OPTION_VALUE_IMAGES_CONTENT,
                                'actionName' => 'render',
                            ],
                            [
                                'targetName' =>
                                    $this->form . '.' . $this->form . '.'
                                    . static::OPTION_VALUE_IMAGES_MODAL_INDEX . '.' . static::OPTION_VALUE_IMAGES_CONTENT,
                                'actionName' => 'loadImagesData',
                                'params'     => [
                                    [
                                        'provider'     => '${ $.provider }',
                                        'dataScope'    => '${ $.dataScope }',
                                        'loadImageUrl' => $this->urlBuilder->getUrl(
                                            'mageworx_optionfeatures/form_image/load'
                                        ),
                                        'formName'     => $this->form,
                                    ],
                                ],
                            ],
                        ],
                        'title'         => __('Images'),
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * The custom option value fields config
     *
     * @return array
     */
    protected function getImagesHiddenFieldConfig()
    {
        $fields                = [];
        $fields['images_data'] = $this->getImagesDataConfig(151);

        return $fields;
    }

    /**
     * Upload image field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getImagesDataConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Hidden::NAME,
                        'sortOrder'     => $sortOrder,
                        'visible'       => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Adds additional options config
     */
    protected function addImageModeConfig()
    {
        $groupCustomOptionsName    = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName       = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        // Add fields to the option
        $additionalOptionFields                                                    = $this->getAdditionalOptionFields();
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $additionalOptionFields
        );
    }

    /**
     * Define additional fields for the option
     *
     * @return array
     */
    protected function getAdditionalOptionFields()
    {
        $fields = [
            static::KEY_OPTION_GALLERY_DISPLAY_MODE => $this->getOptionGalleryDisplayModeFieldsConfig(),
            static::KEY_OPTION_IMAGE_MODE           => $this->getOptionImageModeFieldConfig(),
        ];

        return $fields;
    }

    /**
     * Get config for the option gallery field
     *
     * @return array
     */
    protected function getOptionGalleryDisplayModeFieldsConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Option Gallery Display Mode'),
                        'componentType' => Field::NAME,
                        'component'     => 'MageWorx_OptionFeatures/js/element/option-type-filtered-select',
                        'formElement'   => Select::NAME,
                        'dataScope'     => static::KEY_OPTION_GALLERY_DISPLAY_MODE,
                        'dataType'      => Number::NAME,
                        'sortOrder'     => 80,
                        'options'       => [
                            0 => [
                                'label' => __('Disabled'),
                                'value' => 0,
                            ],
                            1 => [
                                'label' => __('Beside Option'),
                                'value' => 1,
                            ],
                            2 => [
                                'label' => __('Once Selected'),
                                'value' => 2,
                            ],
                        ],
                        'disableLabel'  => true,
                        'multiple'      => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the Option Image Mode select
     *
     * @return array
     */
    protected function getOptionImageModeFieldConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Image Mode'),
                        'componentType' => Field::NAME,
                        'component'     => 'MageWorx_OptionFeatures/js/element/option-type-filtered-select',
                        'formElement'   => Select::NAME,
                        'dataScope'     => static::KEY_OPTION_IMAGE_MODE,
                        'dataType'      => Number::NAME,
                        'sortOrder'     => 90,
                        'options'       => [
                            0 => [
                                'label' => __('Disabled'),
                                'value' => static::OPTION_IMAGE_MODE_DISABLED,
                            ],
                            1 => [
                                'label' => __('Replace'),
                                'value' => static::OPTION_IMAGE_MODE_REPLACE,
                            ],
                        ],
                        'disableLabel'  => true,
                        'multiple'      => false,
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
