<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionTemplates\Plugin;

class DuplicatePager
{
    /**
     * @var \MageWorx\OptionTemplates\Helper\Data
     */
    protected $templateHelper;

    /**
     * DuplicatePager constructor.
     *
     * @param \MageWorx\OptionTemplates\Helper\Data $templateHelper
     */
    public function __construct(\MageWorx\OptionTemplates\Helper\Data $templateHelper)
    {
        $this->templateHelper = $templateHelper;
    }

    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject,
        $result
    ) {
        if (isset($result['custom_options']['children']['options']['arguments']['data']['config'])
            && is_array($result['custom_options']['children']['options']['arguments']['data']['config'])) {
            $templateName = ($this->templateHelper->getModuleVersion(
                    'Magento_Ui'
                ) < '101.0.0') ? "collapsible-21x" : "collapsible-22x";

            $pathTemplate = "MageWorx_OptionTemplates/dynamic-rows/templates/".$templateName;
            $result['custom_options']['children']['options']['arguments']['data']['config']['template'] = $pathTemplate;
        }

        return $result;
    }
}