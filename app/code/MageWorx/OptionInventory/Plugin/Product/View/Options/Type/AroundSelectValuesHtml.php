<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Plugin\Product\View\Options\Type;

use \Magento\Catalog\Block\Product\View\Options\Type\Select;

/**
 * Class AroundSelectValuesHtml.
 * This plugin add stock message to options, disable|hide options.
 *
 * @package MageWorx\OptionInventory\Plugin\Product\View\Options\Type
 */
class AroundSelectValuesHtml
{
    /**
     * @var \MageWorx\OptionInventory\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var \Zend\Stdlib\StringWrapper\MbString
     */
    protected $mbString;

    /**
     * AroundSelectValuesHtml constructor.
     *
     * @param \MageWorx\OptionInventory\Helper\Data $helperData
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     * @param \Zend\Stdlib\StringWrapper\MbString $mbString
     */
    public function __construct(
        \MageWorx\OptionInventory\Helper\Data $helperData,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper,
        \Zend\Stdlib\StringWrapper\MbString $mbString
    ) {
        $this->helperData = $helperData;
        $this->stockHelper = $stockHelper;
        $this->mbString = $mbString;
    }

    /**
     * @param Select $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetValuesHtml(Select $subject, \Closure $proceed)
    {
        $isDisabledOutOfStockOptions = $this->helperData->isDisabledOutOfStockOptions();

        $result = $proceed();
        $option = $subject->getOption();

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        $this->mbString->setEncoding('UTF-8', 'html-entities');
        $result = $this->mbString->convert($result);
        
        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $count = 1;
        foreach ($option->getValues() as $value) {
            $count++;

            if ($option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
                $option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
            ) {
                $element = $elementSelect = $elementTitle =
                    $xpath->query('//option[@value="'.$value->getId().'"]')->item(0);
            }

            if ($option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
                $option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
            ) {
                $element = $xpath
                    ->query('//div/div[descendant::label[@for="options_'.$option->getId().'_'.$count.'"]]')->item(0);
                $elementSelect = $element->getElementsByTagName('input')->item(0);
                $elementTitle = $xpath->query('//label[@for="options_'.$option->getId().'_'.$count.'"]')->item(0);
            }

            if ($option->getType() == 'swatch' || $option->getType() == 'multiswatch') {
                continue;
            }

            $isOutOfStockOption = $this->stockHelper->isOutOfStockOption($value);
            if ($isOutOfStockOption) {
                if (!$isDisabledOutOfStockOptions) {
                    $this->stockHelper->hideOutOfStockOption($element);
                    continue;
                } else {
                    $this->stockHelper->disableOutOfStockOption($elementSelect);
                }
            }

            $stockMessage = $this->stockHelper->getStockMessage($value, $option->getProductId());
            $this->stockHelper->setStockMessage($dom, $elementTitle, $stockMessage);
        }

        $resultBody = $dom->getElementsByTagName('body')->item(0);//$dom->saveHTML();
        $result = $this->getInnerHtml($resultBody);

        return $result;
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node)
    {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }
}
