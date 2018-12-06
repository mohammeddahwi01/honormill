<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin\Cart\Item;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer as OriginalRenderer;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImagesCollection;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\CollectionFactory as ImagesCollectionFactory;
use MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier\Features;

/**
 * Class Renderer
 * @package MageWorx\OptionFeatures\Plugin\Cart\Item
 *
 * Main goal to replace the quote items image in the cart page to the corresponding image based on the custom options
 * selection.
 */
class Renderer
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var ImagesCollectionFactory
     */
    protected $imagesCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \MageWorx\OptionBase\Helper\Data
     */
    protected $baseHelper;

    /**
     * Renderer constructor.
     * @param LayoutInterface $layout
     * @param ImagesCollectionFactory $imagesCollectionFactory
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        LayoutInterface $layout,
        ImagesCollectionFactory $imagesCollectionFactory,
        Helper $helper,
        BaseHelper $baseHelper
    ) {
        $this->layout = $layout;
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
    }

    /**
     * Change main product image for the corresponding image from custom options in the regular cart page
     * (not cart in sidebar)
     *
     * If you searching for the cart sidebar images replacer
     * @see \MageWorx\OptionFeatures\Plugin\Checkout\CustomerData\ItemPool
     *
     * @param OriginalRenderer $subject
     * @param \Closure $proceed
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function aroundGetImage(
        OriginalRenderer $subject,
        \Closure $proceed,
        Product $product,
        $imageId,
        $attributes = []
    ) {
        /** @var QuoteItem $quoteItem */
        $quoteItem = $subject->getItem();
        $infoBuyRequest = $quoteItem->getOptionByCode('info_buyRequest');
        if (!$infoBuyRequest || !$infoBuyRequest->getValue()) {
            $result = $proceed($product, $imageId, $attributes);
            return $result;
        }

        $optionValues = $this->baseHelper->decodeBuyRequestValue($infoBuyRequest->getValue());
        if (empty($optionValues['options'])) {
            $result = $proceed($product, $imageId, $attributes);
            return $result;
        }

        $processImageModes = [
            Features::OPTION_IMAGE_MODE_REPLACE,
        ];
        $optionsShouldBeProcessed = [];
        foreach ($optionValues['options'] as $optionId => $value) {
            /** @var \Magento\Catalog\Model\Product\Option $option */
            $option = $product->getOptionById($optionId);
            /** @var array $processImageModes */
            if (!empty($option[Features::KEY_OPTION_IMAGE_MODE]) &&
                in_array($option[Features::KEY_OPTION_IMAGE_MODE], $processImageModes)
            ) {
                $optionsShouldBeProcessed[] = $option;
            }
        }

        // Do nothing with product without replace mode
        if (empty($optionsShouldBeProcessed)) {
            $result = $proceed($product, $imageId, $attributes);
            return $result;
        }

        /** @var \Magento\Catalog\Block\Product\Image $imageBlock */
        $imageBlock = $this->layout->createBlock('\Magento\Catalog\Block\Product\Image')
            ->setTemplate('Magento_Catalog::product/image_with_borders.phtml');
        /** @var array $imageData */
        $imageData = $this->getSelectedOptionsImageData($optionsShouldBeProcessed, $quoteItem, $attributes);
        if (!$imageData || empty($imageData)) {
            $result = $proceed($product, $imageId, $attributes);
            return $result;
        }
        $imageBlock->addData($imageData);

        return $imageBlock;
    }

    /**
     * Search most suitable image using sort order and returns its data in array:
     * 'image_url' => string,
     * 'width' => int,
     * 'height' => int,
     * 'label' => string,
     * 'resized_image_width' => int,
     * 'resized_image_height' => int,
     * 'custom_attributes' => string (!)
     *
     * @important Method uses recursion and can call itself if suitable image is not found
     * in the current option or value
     *
     * @param \Magento\Catalog\Model\Product\Option[] $optionsShouldBeProcessed
     * @param QuoteItem $quoteItem
     * @param array $customAttributes array of html custom attributes for the <img>
     * @return array
     */
    protected function getSelectedOptionsImageData(
        array $optionsShouldBeProcessed,
        QuoteItem $quoteItem,
        $customAttributes = []
    ) {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        $imageHeight = 165;
        $imageWidth = 165;
        $customAttributesAsString = $this->getCustomAttributes($customAttributes);

        $sortedOptions = $this->helper->sortOptions($optionsShouldBeProcessed);
        /** @var \Magento\Catalog\Model\Product\Option $lastOption */
        $lastOption = end($sortedOptions);
        $lastOptionId = $lastOption->getId();
        /** @var \Magento\Quote\Model\Quote\Item\Option $quoteItemOption */
        $quoteItemOption = $quoteItem->getOptionByCode('option_' . $lastOptionId);
        if (!$quoteItemOption && !empty($optionsShouldBeProcessed)) {
            return $this->renew($optionsShouldBeProcessed, $quoteItem, $customAttributes);
        }

        $optionValue = $quoteItemOption->getValue();
        if (!$optionValue) {
            return $this->renew($optionsShouldBeProcessed, $quoteItem, $customAttributes);
        }
        $optionValuesReversed = array_reverse(explode(',', $optionValue));
        foreach ($optionValuesReversed as $value) {
            $valueModel = $lastOption->getValueById($value);
            if (!$valueModel || !$valueModel->getId()) {
                continue;
            }
            /** @var ImagesCollection $imageCollection */
            $imageCollection = $this->imagesCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'mageworx_option_type_id',
                    $valueModel->getData('mageworx_option_type_id')
                )->addFieldToFilter(
                    'replace_main_gallery_image',
                    1
                );
            /** @var ImageModel $imageModel */
            $imageModel = $imageCollection->getFirstItem();
            if (!$imageModel || !$imageModel->getValue()) {
                continue;
            }

            $imageUrl = $this->helper->getImageUrl($imageModel->getValue(), $imageHeight, $imageWidth);
            $data = [
                'image_url' => $imageUrl,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'label' => $imageModel->getAlt(),
                'resized_image_width' => $imageWidth,
                'resized_image_height' => $imageHeight,
                'custom_attributes' => $customAttributesAsString,
            ];

            return $data;
        }

        return $this->renew($optionsShouldBeProcessed, $quoteItem, $customAttributes);
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getCustomAttributes($attributes = [])
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }

        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Used for recursion call of the getSelectedOptionsImageData method
     * validate input data and breaks recursion if an input array (options) is empty
     *
     * @param array $optionsShouldBeProcessed
     * @param QuoteItem $quoteItem
     * @param array $customAttributes
     * @return array|null
     */
    private function renew(
        array $optionsShouldBeProcessed,
        QuoteItem $quoteItem,
        array $customAttributes
    ) {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        array_pop($optionsShouldBeProcessed);

        return $this->getSelectedOptionsImageData($optionsShouldBeProcessed, $quoteItem, $customAttributes);
    }
}
