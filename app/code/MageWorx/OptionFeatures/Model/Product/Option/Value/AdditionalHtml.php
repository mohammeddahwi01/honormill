<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Product\Option\Value;

use Magento\Framework\App\RequestInterface as Request;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\State;
use Zend\Stdlib\StringWrapper\MbString;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class AdditionalHtml
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var MbString
     */
    protected $mbString;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendQuoteSession;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var array
     */
    protected $optionsQty;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @param Request $request
     * @param Helper $helper
     * @param State $state
     * @param Cart $cart
     * @param MbString $mbString
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Request $request,
        Cart $cart,
        State $state,
        Helper $helper,
        MbString $mbString,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        PricingHelper $pricingHelper
    ) {
        $this->request = $request;
        $this->cart = $cart;
        $this->state = $state;
        $this->helper = $helper;
        $this->mbString = $mbString;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param \DOMDocument $dom
     * @param Option $option
     * @return void
     */
    public function getAdditionalHtml($dom, $option)
    {
        if (!$this->helper->isQtyInputEnabled() || !$dom || !$option) {
            return;
        }

        $this->dom = $dom;
        $this->option = $option;
        $this->optionsQty = $this->getQuoteItemOptionsQty();

        $body = $this->dom->documentElement->firstChild;

        if ($this->isCheckboxWithQtyInput($this->option)) {
            $this->addHtmlToMultiSelectionOption();
        } else {
            if ($this->isDropdownWithQtyInput($this->option) || $this->isRadioWithQtyInput($this->option)) {
                $qtyInput = $this->getHtmlForSingleSelectionOption();
            } elseif ($this->isMultiselect($this->option) || !$this->option->getQtyInput()) {
                $qtyInput = $this->getDefaultHtml();
            } else {
                return;
            }

            $tpl = new \DOMDocument();
            $tpl->loadHtml($qtyInput);
            $body->appendChild($this->dom->importNode($tpl->documentElement, true));
        }

        libxml_clear_errors();

        return;
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    /**
     * @param int $optionValue
     * @return string
     */
    protected function getOptionQty($optionValue)
    {
        $qty = 0;
        if (isset($this->optionsQty[$this->option->getOptionId()])) {
            if (!is_array($this->optionsQty[$this->option->getOptionId()])) {
                $qty = $this->optionsQty[$this->option->getOptionId()];
            } else {
                if (isset($this->optionsQty[$this->option->getOptionId()][$optionValue])) {
                    $qty = $this->optionsQty[$this->option->getOptionId()][$optionValue];
                }
            }
        }
        return $qty;
    }

    /**
     * Get qty input from buyRequest
     *
     * @return int
     */
    protected function getQuoteItemOptionsQty()
    {
        $optionsQty = [];
        if ($this->request->getControllerName() != 'product') {
            $quoteItemId = (int)$this->request->getParam('id');
            if ($quoteItemId) {
                if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                    $quoteItem = $this->backendQuoteSession->getQuote()->getItemById($quoteItemId);
                } else {
                    $quoteItem = $this->cart->getQuote()->getItemById($quoteItemId);
                }
                if ($quoteItem) {
                    $buyRequest = $quoteItem->getBuyRequest();
                    if ($buyRequest) {
                        $optionsQty = $buyRequest->getOptionsQty();
                    }
                }
            }
        }
        return $optionsQty;
    }

    /**
     * Get qty input html for checkbox (multiswatch in future)
     *
     * @return void
     */
    protected function addHtmlToMultiSelectionOption()
    {
        $count = 1;
        foreach ($this->option->getValues() as $value) {
            $count++;
            $optionValueQty = $this->getOptionQty($value->getOptionTypeId());
            $qtyInput = '<div class="label-qty" style="display: inline-block; padding: 5px; margin-left: 3em">' .
                '<b>Qty: </b>' .
                '<input name="options_qty[' . $this->option->getId() . '][' . $value->getOptionTypeId() . ']"' .
                ' id="options_' . $this->option->getId() . '_' . $value->getOptionTypeId() . '_qty"' .
                ' class="qty mageworx-option-qty" type="number" value="' . $optionValueQty . '" min="0" disabled' .
                ' style="width: 3em; text-align: center; vertical-align: middle;"' .
                ' data-parent-selector="options[' . $this->option->getId() . '][' . $value->getOptionTypeId() . ']"' .
                ' /></div>';

            $tpl = new \DOMDocument('1.0', 'UTF-8');
            $tpl->loadHtml($qtyInput);

            $xpath    = new \DOMXPath($this->dom);
            $idString = 'options_' . $this->option->getId() . '_' . $count;
            $input    = $xpath->query("//*[@id='$idString']")->item(0);

            $input->setAttribute('style', 'vertical-align: middle');
            $input->parentNode->appendChild($this->dom->importNode($tpl->documentElement, true));
        }
    }

    /**
     * Get qty input html for dropdown, radiobutton, swatch
     *
     * @return string
     */
    protected function getHtmlForSingleSelectionOption()
    {
        $optionQty = $this->getOptionQty($this->option->getId());
        return '<div class="label-qty" style="display: inline-block; padding: 5px;"><b>Qty: </b>'
            . '<input name="options_qty[' . $this->option->getId() . ']"'
            . ' id="options_' . $this->option->getId() . '"'
            . ' class="qty mageworx-option-qty" type="number" value="' . $optionQty . '" min="0" disabled'
            . ' style="width: 3em; text-align: center; vertical-align: middle;"'
            . ' data-parent-selector="options[' . $this->option->getId() . ']" />' . '</div>';

    }

    /**
     * Get qty input html for multiselect
     *
     * @return string
     */
    protected function getDefaultHtml()
    {
        return '<input name="options_qty[' . $this->option->getId() . ']" id="options_'
            . $this->option->getId() . '" class="qty mageworx-option-qty" type="hidden" value="1"'
            . ' style="width: 3em; text-align: center; vertical-align: middle;"'
            . ' data-parent-selector="options[' . $this->option->getId() . ']"/>';
    }

    /**
     * Check if option is checkbox and QtyInput is set
     *
     * @return bool
     */
    protected function isCheckboxWithQtyInput($option)
    {
        return $option->getType() == Option::OPTION_TYPE_CHECKBOX && $option->getQtyInput();
    }

    /**
     * Check if option is dropdown/swatch and QtyInput is set
     *
     * @return bool
     */
    protected function isDropdownWithQtyInput($option)
    {
        return $option->getType() == Option::OPTION_TYPE_DROP_DOWN && $option->getQtyInput();
    }

    /**
     * Check if option is radio and QtyInput is set
     *
     * @return bool
     */
    protected function isRadioWithQtyInput($option)
    {
        return $option->getType() == Option::OPTION_TYPE_RADIO && $option->getQtyInput();
    }

    /**
     * Check if option is multiselect
     *
     * @return bool
     */
    protected function isMultiselect($option)
    {
        return $option->getType() == Option::OPTION_TYPE_MULTIPLE;
    }
}
