<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Block;

use Magento\Framework\Registry;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Config\Features as FeaturesConfig;

class Features extends Template
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FeaturesConfig
     */
    protected $featuresConfig;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param BaseHelper $baseHelper
     * @param Registry $registry
     * @param FeaturesConfig $featuresConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Helper $helper,
        SystemHelper $systemHelper,
        BaseHelper $baseHelper,
        Registry $registry,
        FeaturesConfig $featuresConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->systemHelper = $systemHelper;
        $this->baseHelper = $baseHelper;
        $this->registry = $registry;
        $this->featuresConfig = $featuresConfig;
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $data = [
            'question_image' => $this->getViewFileUrl('MageWorx_OptionFeatures::image/question.png'),
            'value_description_enabled' => $this->helper->isDescriptionEnabled(),
            'option_description_enabled' => $this->helper->isOptionDescriptionEnabled(),
            'option_description_mode' => $this->helper->getOptionDescriptionMode(),
            'option_description_modes' => [
                'disabled' => Helper::OPTION_DESCRIPTION_DISABLED,
                'tooltip' => Helper::OPTION_DESCRIPTION_TOOLTIP,
                'text' => Helper::OPTION_DESCRIPTION_TEXT,
            ]
        ];

        return $this->jsonEncoder->encode($data);
    }

    /**
     * @param string $area
     * @return string
     */
    public function getIsDefaultJsonData($area)
    {
        $router = '';
        if ($this->getRequest()->getRouteName() == 'checkout') {
            $router = 'checkout';
        }
        if ($this->getRequest()->getRouteName() == 'sales'
            && $this->getRequest()->getControllerName() == 'order_create'
        ) {
            $router = 'admin_order_create';
        }

        $data = [
            'is_default_values' => $this->helper->isDefaultEnabled() ?
                $this->featuresConfig->getIsDefaultArray($this->registry->registry('product')) :
                [],
            'is_default_enabled' => $this->helper->isDefaultEnabled(),
            'area' => $area == '' ? 'frontend' : $area,
            'router' => $router
        ];

        return $this->jsonEncoder->encode($data);
    }
}
