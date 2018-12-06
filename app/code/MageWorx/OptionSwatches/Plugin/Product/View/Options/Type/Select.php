<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionSwatches\Plugin\Product\View\Options\Type;

use Magento\Catalog\Block\Product\View\Options\Type\Select as TypeSelect;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;

class Select
{
    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var BasePriceHelper
     */
    protected $basePriceHelper;

    /**
     * @param PricingHelper $pricingHelper
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param BasePriceHelper $basePriceHelper
     * @param State $state
     */
    public function __construct(
        PricingHelper $pricingHelper,
        Helper $helper,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        State $state
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->basePriceHelper = $basePriceHelper;
        $this->state = $state;
    }

    /**
     * Return html for control element
     *
     * @param TypeSelect $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetValuesHtml(TypeSelect $subject, \Closure $proceed)
    {
        $_option = $subject->getOption();
        if (($_option->getType() == Option::OPTION_TYPE_DROP_DOWN ||
                $_option->getType() == Option::OPTION_TYPE_MULTIPLE) &&
            $this->state->getAreaCode() !== Area::AREA_ADMINHTML &&
            $_option->getIsSwatch()
        ) {
            $renderSwatchOptions = '';
            /** @var ProductCustomOptionValuesInterface $_value */
            foreach ($_option->getValues() as $_value) {
                $renderSwatchOptions .= $this->getOptionSwatchHtml($_option, $_value);
            }
            $renderSwatchSelect = $this->getOptionSwatchHiddenHtml($subject);
            $divClearfix = '<div class="swatch-attribute-options clearfix">';
            $divStart = '<div class="swatch-attribute size">';
            $divEnd = '</div>';

            $selectHtml = $divStart . $divClearfix . $renderSwatchOptions . $renderSwatchSelect . $divEnd . $divEnd;

            return $selectHtml;
        }

        return $proceed();
    }

    /**
     * Get html for visible part of swatch element
     *
     * @param Option $option
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface $optionValue
     * @return string
     */
    private function getOptionSwatchHtml($option, $optionValue)
    {
        $type = $optionValue->getBaseImageType() ? $optionValue->getBaseImageType() : 'text';
        $optionValue->getTitle() ? $label = $optionValue->getTitle() : $label = '';
        $thumb = $this->helper->getThumbImageUrl(
            $optionValue->getTooltipImage(),
            Helper::IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE
        );
        $value = $this->helper->getThumbImageUrl(
            $optionValue->getBaseImage(),
            Helper::IMAGE_MEDIA_ATTRIBUTE_SWATCH_IMAGE
        );
        if (!$value) {
            $value = $label;
        }

        $description = '';
        if ($this->helper->isOptionValueDescriptionEnabled()) {
            $description = $optionValue->getDescription();
        }

        if (!$optionValue->getPrice()) {
            $price = 0;
        } else {
            if ($optionValue->getPriceType() == 'percent') {
                $productFinalPrice = $this->basePriceHelper->getTaxPrice(
                    $option->getProduct(),
                    $option->getProduct()->getPrice()
                );
                $price = $productFinalPrice * $optionValue->getPrice() / 100;
            } else {
                $price = $this->basePriceHelper->getTaxPrice(
                    $option->getProduct(),
                    $optionValue->getPrice()
                );
            }
        }

        $attributes = ' option-type="' . $type . '"' .
            ' option-id="' . $option->getId() . '"' .
            ' option-type-id="' . $optionValue->getId() . '"' .
            ' option-label="' . $label . '"' .
            ' option-description="' . $description . '"' .
            ' option-price="' . $price . '"' .
            ' option-tooltip-thumb="' . $thumb . '"';
        $html = '';
        $isStocktab = ($optionValue->getIsStocktab()) ? ' st-tab' : '';
        $isCustomtab = ($optionValue->getIsCustomtab()) ? ' ct-tab' : '';
        if ($isCustomtab != '' || $isStocktab != '')  {
            switch ($type) {
                case 'text':
                    $html .= '<div class="mageworx-swatch-option act-swatch text '.$isStocktab.$isCustomtab.'"';
                    $html .= $attributes;
                    $html .= '>';
                    $html .= '<div>'.$label.'</div>';
                    $html .= '</div>';
                    break;
                case 'image':
                case 'color':
                    $html .= '<div class="mageworx-swatch-option act-swatch image '.$isStocktab.$isCustomtab.'"';
                    $html .= $attributes;
                    $html .= '>';
                    $html .= '<div><img src="'.$value.'" alt="'.$label.'"></div>';
                    $html .= '</div>';
                    break;
                default:
                    $html .= '<div class="mageworx-swatch-option act-swatch '.$isStocktab.$isCustomtab.'"';
                    $html .= $attributes;
                    $html .= '>';
                    $html .= $label;
                    $html .= '</div>';
                    break;
            }
        }

        return $html;
    }

    /**
     * Get html for hidden part of swatch element
     *
     * @param TypeSelect $subject
     * @return string
     */
    private function getOptionSwatchHiddenHtml($subject)
    {
        $_option = $subject->getOption();
        $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $subject->getProduct()->getStore();

        $require = $_option->getIsRequire() ? ' required' : '';
        $extraParams = '';
        /** @var \Magento\Framework\View\Element\Html\Select $select */
        $select = $subject->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => 'select_' . $_option->getId(),
                'class' => $require . ' mageworx-swatch hidden product-custom-option admin__control-select',
            ]
        );
        if ($_option->getType() == Option::OPTION_TYPE_DROP_DOWN && $_option->getIsSwatch()) {
            $select->setName('options[' . $_option->getId() . ']')->addOption('', __('-- Please Select --'));
        } else {
            $select->setName('options[' . $_option->getId() . '][]');
            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        }
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface $_value */
        foreach ($_option->getValues() as $_value) {
            $priceStr = '';
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(), $store, false)]
            );
        }
        if ($_option->getType() == Option::OPTION_TYPE_MULTIPLE && $_option->getIsSwatch()) {
            $extraParams = ' multiple="multiple"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }
}
