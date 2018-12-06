<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Plugin\Checkout;

use Magento\Checkout\Model\DefaultConfigProvider as OriginalDefaultConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImagesCollection;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\CollectionFactory as ImagesCollectionFactory;
use MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier\Features;

/**
 * Class DefaultConfigProvider
 * @package MageWorx\OptionFeatures\Plugin\Checkout
 *
 * Main goal is to replace quote item image in the checkout page to the corresponding image based on the custom options
 * selection.
 */
class DefaultConfigProvider
{
    /**
     * @var ImagesCollectionFactory
     */
    protected $imagesCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    public function __construct(
        ImagesCollectionFactory $imagesCollectionFactory,
        Helper $helper
    ) {
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        $this->helper = $helper;
    }

    /**
     * Used for the image replacement in the checkout review section
     *
     * @param OriginalDefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(OriginalDefaultConfigProvider $subject, array $result)
    {
        $processImageModes = [
            Features::OPTION_IMAGE_MODE_REPLACE,
        ];

        if (empty($result['quoteItemData'])) {
            return $result;
        }

        foreach ($result['quoteItemData'] as $index => $quoteItemData) {
            // Do nothing for product without options
            if (empty($quoteItemData['product']['options'])) {
                continue;
            }

            $optionsShouldBeProcessed = [];
            // Check image mode in all options
            foreach ($quoteItemData['product']['options'] as $option) {
                if (!empty($option[Features::KEY_OPTION_IMAGE_MODE]) &&
                    in_array($option[Features::KEY_OPTION_IMAGE_MODE], $processImageModes)
                ) {
                    $optionsShouldBeProcessed[] = $option;
                }
            }

            // Do nothing with product without replace mode
            if (empty($optionsShouldBeProcessed)) {
                continue;
            }

            $imageUrl = $this->getSelectedOptionsImageUrl($index, $result, $optionsShouldBeProcessed);
            if ($imageUrl) {
                $result['quoteItemData'][$index]['thumbnail'] = $imageUrl;
                $result['imageData'][$quoteItemData['item_id']]['src'] = $imageUrl;
            }
        }

        return $result;
    }

    /**
     * Search most suitable image using sort order and returns its URL
     *
     * @important Method uses recursion and can call itself if suitable image is not found
     * in the current option or value
     *
     * @param int $index Quote Item index in config
     * @param array $result Config
     * @param \Magento\Catalog\Model\Product\Option[] $optionsShouldBeProcessed Options with processable image mode
     * @return string|null
     * @throws NoSuchEntityException
     */
    private function getSelectedOptionsImageUrl($index, $result, $optionsShouldBeProcessed)
    {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        $imageWidth = 75;
        $imageHeight = 75;
        $sortedOptions = $this->helper->sortOptions($optionsShouldBeProcessed);
        /** @var \Magento\Catalog\Model\Product\Option $lastOption */
        $lastOption = end($sortedOptions);
        $lastOptionId = $lastOption->getId();
        $quoteItemId = $result['quoteItemData'][$index]['item_id'];
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->findQuoteItemByIdInConfig($quoteItemId, $result);
        if (!$quoteItem) {
            return null;
        }

        /** @var \Magento\Quote\Model\Quote\Item\Option $quoteItemOption */
        $quoteItemOption = $quoteItem->getOptionByCode('option_' . $lastOptionId);
        if (empty($quoteItemOption)) {
            return $this->renew($index, $result, $optionsShouldBeProcessed);
        }

        $optionValue = $quoteItemOption->getValue();
        $optionValuesReversed = array_reverse(explode(',', $optionValue));
        foreach ($optionValuesReversed as $value) {
            /** @var \Magento\Catalog\Model\Product\Option\Value $valueModel */
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
            if (!$imageModel->getId() || !$imageModel->getValue()) {
                continue;
            }
            $imageUrl = $this->helper->getImageUrl($imageModel->getValue(), $imageHeight, $imageWidth);

            return $imageUrl;
        }

        return $this->renew($index, $result, $optionsShouldBeProcessed);
    }

    /**
     * Return quote item from config by its id
     *
     * @param int $id
     * @param array $config
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    private function findQuoteItemByIdInConfig($id, array $config)
    {
        /** @var \Magento\Quote\Model\Quote\Item[] $items */
        $items = $config['quoteData']['items'];
        foreach ($items as $index => $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Used for recursion call of the getSelectedOptionsImageUrl method
     * validate input data and breaks recursion if an input array (options) is empty
     *
     * @param $index
     * @param $result
     * @param $optionsShouldBeProcessed
     * @return array|null
     */
    private function renew($index, $result, $optionsShouldBeProcessed)
    {
        if (empty($optionsShouldBeProcessed)) {
            return null;
        }

        array_pop($optionsShouldBeProcessed);

        return $this->getSelectedOptionsImageUrl($index, $result, $optionsShouldBeProcessed);
    }
}
