<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Block\Product\View;

use Magento\Catalog\Api\ProductCustomOptionTypeListInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\View\Element\Template;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\Product\Option\Value\Media\Config as MediaConfig;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImagesCollection;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\CollectionFactory as ImagesCollectionFactory;
use MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier\Features;

/**
 * Class Wrapper
 * @package MageWorx\OptionSwatches\Block\Product\View
 *
 * Main goal is to provide image data for the frontend widgets:
 * @see MageWorx/OptionFeatures/view/frontend/web/js/swatches.js
 * @see MageWorx/OptionFeatures/view/frontend/web/js/swatches/additional.js
 */
class Wrapper extends Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ImagesCollectionFactory
     */
    protected $imagesCollectionFactory;

    /**
     * @var array
     */
    protected $mediaAttributes = [];

    /**
     * @var ProductCustomOptionTypeListInterface
     */
    protected $customOptionTypeList;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * Wrapper constructor.
     * @param Template\Context $context
     * @param Helper $helper
     * @param ImagesCollectionFactory $imagesCollectionFactory
     * @param ProductCustomOptionTypeListInterface $customOptionTypeList
     * @param MediaConfig $mediaConfig
     * @param ImageFactory $imageFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Helper $helper,
        ImagesCollectionFactory $imagesCollectionFactory,
        ProductCustomOptionTypeListInterface $customOptionTypeList,
        MediaConfig $mediaConfig,
        ImageFactory $imageFactory,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        $this->customOptionTypeList = $customOptionTypeList;
        $this->mediaConfig = $mediaConfig;
        $this->imageFactory = $imageFactory;
    }

    /**
     * @return string
     */
    public function getJsonParams()
    {
        $data = [];

        return json_encode($data);
    }

    /**
     * Returns JSON config for the frontend swatch-widgets
     *
     * @important Do not remove any data from method without testing frontend! because frontend widget depends on it!
     *
     * @see MageWorx/OptionSwatches/view/frontend/web/js/swatches.js
     * @see MageWorx/OptionSwatches/view/frontend/web/js/swatches/additional.js
     *
     * @return mixed|string|void
     */
    public function getAllOptionsJson()
    {
        $data = [];

        /** @var \Magento\Catalog\Block\Product\View $productMainBlock */
        $productMainBlock = $this->getLayout()->getBlockSingleton('Magento\Catalog\Block\Product\View');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productMainBlock->getProduct();
        if (!$product || !$product->getId()) {
            return json_encode($data);
        }

        $options = $product->getOptions();
        $data['options'] = [];
        /** @var ProductOption $option */
        foreach ($options as $option) {
            /** @var int $optionId */
            $optionId = $option->getId();
            if (!in_array($option->getType(), $this->getOptionTypesWithImages()) || !$option->getValues()) {
                continue;
            }
            $data['options'][$optionId] = [
                'type' => $option->getType(),
                'mageworx_option_gallery' => $option->getData('mageworx_option_gallery'),
                Features::KEY_OPTION_IMAGE_MODE => $option->getData(Features::KEY_OPTION_IMAGE_MODE),
                'sort_order' => $option->getSortOrder(),
            ];
            $values = $option->getValues();

            /**
             * @var \Magento\Catalog\Model\Product\Option\Value $value
             */
            foreach ($values as $valueId => $value) {
                /** @var ImagesCollection $collection */
                $collection = $this->imagesCollectionFactory
                    ->create()
                    ->addFieldToFilter(
                        'mageworx_option_type_id',
                        $value->getData('mageworx_option_type_id')
                    );
                $data['options'][$optionId]['values'][$valueId]['sort_order'] = $value->getSortOrder();
                foreach ($collection->getItems() as $collectionItem) {
                    $data['options'][$optionId]['values'][$valueId]['images'][$collectionItem->getOptionTypeImageId()] = [
                        'value_id' => $collectionItem->getOptionTypeImageId(),
                        'option_type_id' => $collectionItem->getMageworxOptionTypeId(),
                        'position' => $collectionItem->getSortOrder(),
                        'file' => $collectionItem->getValue(),
                        'label' => $collectionItem->getTitleText(),
                        'custom_media_type' => $collectionItem->getMediaType(),
                        'color' => $collectionItem->getColor(),
                        ImageModel::COLUMN_HIDE_IN_GALLERY =>
                            $collectionItem->getData(ImageModel::COLUMN_HIDE_IN_GALLERY),
                        'url' => $this->helper->getThumbImageUrl(
                            $collectionItem->getValue(),
                            Helper::IMAGE_MEDIA_ATTRIBUTE_BASE_IMAGE
                        ),
                        ImageModel::COLUMN_REPLACE_MAIN_GALLERY_IMAGE =>
                            $collectionItem->getData(ImageModel::COLUMN_REPLACE_MAIN_GALLERY_IMAGE),
                    ];
                    if ($collectionItem->getData(ImageModel::COLUMN_REPLACE_MAIN_GALLERY_IMAGE)) {
                        $data['options'][$optionId]['values'][$valueId]['images'][$collectionItem->getOptionTypeImageId()]['full'] =
                            $this->getImageUrl($collectionItem->getValue());
                        $data['options'][$optionId]['values'][$valueId]['images'][$collectionItem->getOptionTypeImageId()]['img'] =
                            $this->getImageUrl($collectionItem->getValue());
                        $data['options'][$optionId]['values'][$valueId]['images'][$collectionItem->getOptionTypeImageId()]['thumb'] =
                            $this->getImageUrl($collectionItem->getValue(), 'product_page_image_small');
                    }
                }
            }
        }

        $data['option_types'] = $this->getOptionTypes();
        $data['render_images_for_option_types'] = $this->getOptionTypesWithImages();
        $data['option_gallery_type'] = [
            'disabled' => Helper::OPTION_GALLERY_TYPE_DISABLED,
            'beside_option' => Helper::OPTION_GALLERY_TYPE_BESIDE_OPTION,
            'once_selected' => Helper::OPTION_GALLERY_TYPE_ONCE_SELECTED,
        ];

        return json_encode($data);
    }

    /**
     * Return array with option types with images (option gallery)
     *
     * @return array
     */
    public function getOptionTypesWithImages()
    {
        return [
            ProductOption::OPTION_TYPE_DROP_DOWN,
            ProductOption::OPTION_TYPE_RADIO,
            ProductOption::OPTION_TYPE_CHECKBOX
        ];
    }

    /**
     * Get image url for specified type, width or height
     *
     * @param $path
     * @param null $type
     * @param null $height
     * @param null $width
     * @return string
     */
    public function getImageUrl($path, $type = null, $height = null, $width = null)
    {
        if (!$path) {
            return '';
        }

        if ($type) {
            /** @var \Magento\Catalog\Block\Product\View\Gallery $galleryBlock */
            $galleryBlock = $this->getLayout()->getBlockSingleton('Magento\Catalog\Block\Product\View\Gallery');
            $width = $galleryBlock->getImageAttribute($type, 'width');
            $height = $galleryBlock->getImageAttribute($type, 'height');
        } elseif (!$height && !$width) {
            return $this->mediaConfig->getMediaUrl($path);
        } elseif (!$height) {
            $height = $width;
        } elseif (!$width) {
            $width = $height;
        }

        return $this->helper->getImageUrl($path, $height, $width);
    }

    /**
     * Get all available option types in array
     *
     * @return array
     */
    public function getOptionTypes()
    {
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterface[] $typesList */
        $typesList = $this->customOptionTypeList->getItems();
        $optionTypeCodes = [];
        foreach ($typesList as $type) {
            $optionTypeCodes[] = $type->getCode();
        }

        return $optionTypeCodes;
    }

    /**
     * Set media attribute value
     *
     * @param $code
     * @param $value
     */
    protected function setMediaAttributeValue($code, $value)
    {
        $this->mediaAttributes[$code]['value'] = $value;
    }
}
