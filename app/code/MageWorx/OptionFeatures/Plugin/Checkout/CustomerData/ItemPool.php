<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\ItemPool as OriginalItemPool;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImagesCollection;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\CollectionFactory as ImagesCollectionFactory;
use MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier\Features;

/**
 * Class ItemPool
 * @package MageWorx\OptionFeatures\Plugin\Checkout\CustomerData
 *
 * Main goal is to replace quote item image in the cart sidebar to the corresponding image based on the custom options
 * selection.
 */
class ItemPool
{
    /**
     * @var ImagesCollectionFactory
     */
    protected $imagesCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * ItemPool constructor.
     * @param ImagesCollectionFactory $imagesCollectionFactory
     * @param Helper $helper
     */
    public function __construct(
        ImagesCollectionFactory $imagesCollectionFactory,
        Helper $helper
    ) {
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        $this->helper = $helper;
    }

    /**
     * Used for the image replacement in the cart sidebar (not a regular cart page!)
     * For regular cart @see \MageWorx\OptionFeatures\Plugin\Cart\Item\Renderer
     *
     * @param OriginalItemPool $subject
     * @param \Closure $proceed
     * @param QuoteItem $item
     * @return array
     */
    public function aroundGetItemData(OriginalItemPool $subject, \Closure $proceed, QuoteItem $item)
    {
        $result = $proceed($item);
        if (empty($result)) {
            return $result;
        }

        $processImageModes = [
            Features::OPTION_IMAGE_MODE_REPLACE,
        ];

        if (empty($result['options'])) {
            return $result;
        }

        $optionsShouldBeProcessed = [];
        // Check image mode in all options
        foreach ($result['options'] as $optionData) {
            if (empty($optionData['option_id'])) {
                continue;
            }

            $option = $item->getOptionByCode('option_' . $optionData['option_id']);
            if (empty($option)) {
                continue;
            }

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $item->getProduct();
            $productOption = $product->getOptionById($optionData['option_id']);
            if (!empty($productOption[Features::KEY_OPTION_IMAGE_MODE]) &&
                in_array($productOption[Features::KEY_OPTION_IMAGE_MODE], $processImageModes)
            ) {
                $optionsShouldBeProcessed[] = $productOption;
            }
        }

        // Do nothing with product without replace mode
        if (empty($optionsShouldBeProcessed)) {
            return $result;
        }

        $imageData = $this->getSelectedOptionsImageData($optionsShouldBeProcessed, $item);
        if (!empty($imageData)) {
            $result['product_image'] = $imageData;
        }

        return $result;
    }

    /**
     * Search most suitable image using sort order and returns its data in array:
     * 'src' => string image url in pub/media,
     * 'alt' => string,
     * 'width' => int,
     * 'height' => int
     *
     * @important Method uses recursion and can call itself if suitable image is not found
     * in the current option or value
     *
     * @param \Magento\Catalog\Model\Product\Option[] $optionsShouldBeProcessed Options with processable image mode
     * @param QuoteItem $quoteItem
     * @return array
     */
    private function getSelectedOptionsImageData(array $optionsShouldBeProcessed, QuoteItem $quoteItem)
    {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        $imageHeight = 75;
        $imageWidth = 75;
        $sortedOptions = $this->helper->sortOptions($optionsShouldBeProcessed);
        /** @var \Magento\Catalog\Model\Product\Option $lastOption */
        $lastOption = end($sortedOptions);
        $lastOptionId = $lastOption->getId();
        /** @var \Magento\Quote\Model\Quote\Item\Option $quoteItemOption */
        $quoteItemOption = $quoteItem->getOptionByCode('option_' . $lastOptionId);
        if (!$quoteItemOption) {
            return $this->renew($optionsShouldBeProcessed, $quoteItem);
        }

        $optionValue = $quoteItemOption->getValue();
        if (!$optionValue) {
            return $this->renew($optionsShouldBeProcessed, $quoteItem);
        }

        $optionValuesReversed = array_reverse(explode(',', $optionValue));
        foreach ($optionValuesReversed as $value) {
            $valueModel = $lastOption->getValueById($value);
            if (!$valueModel) {
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
                'src' => $imageUrl,
                'alt' => $imageModel->getAlt(),
                'width' => $imageWidth,
                'height' => $imageHeight,
            ];

            return $data;
        }

        return $this->renew($optionsShouldBeProcessed, $quoteItem);
    }

    /**
     * Used for recursion call of the getSelectedOptionsImageData method
     * validate input data and breaks recursion if an input array (options) is empty
     *
     * @param array $optionsShouldBeProcessed
     * @param QuoteItem $quoteItem
     * @return array|null
     */
    private function renew(array $optionsShouldBeProcessed, QuoteItem $quoteItem)
    {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        array_pop($optionsShouldBeProcessed);

        return $this->getSelectedOptionsImageData($optionsShouldBeProcessed, $quoteItem);
    }
}
